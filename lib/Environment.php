<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Meta\LinkTarget;
use Doctrine\RST\Meta\DocumentMetaData;
use Doctrine\RST\Meta\Metas;
use Doctrine\RST\NodeFactory\NodeFactory;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\Templates\TemplateRenderer;
use Doctrine\RST\TextRoles\ReferenceRole;
use Doctrine\RST\TextRoles\TextRole;
use InvalidArgumentException;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function array_filter;
use function array_shift;
use function dirname;
use function implode;
use function in_array;
use function sprintf;
use function strtolower;
use function trim;

class Environment
{
    private Configuration $configuration;

    private ErrorManager $errorManager;

    private UrlGenerator $urlGenerator;

    private int $currentTitleLevel = 0;

    /** @var string[] */
    private $titleLetters = [];

    private string $currentFileName = '';

    private string $currentDirectory = '.';

    private string $targetDirectory = '.';

    private ?string $url = null;

    /** @var array<string, TextRole> */
    private array $textRoles = [];

    private Metas $metas;

    /** @var string[] */
    private array $dependencies = [];

    /** @var string[] */
    private array $unresolvedDependencies = [];

    /** @var string[] */
    private array $originalDependencyNames = [];

    /** @var string[] */
    private array $variables = [];

    /** @var int[] */
    private $levels = [];

    /** @var int[] */
    private $counters = [];

    /** @var string[] */
    private $anonymous = [];

    /** @var InvalidLink[] */
    private array $invalidLinks = [];

    public function __construct(Configuration $configuration, ?Metas $metas = null)
    {
        $this->configuration = $configuration;
        $this->errorManager  = $configuration->getErrorManager();
        $this->urlGenerator  = new UrlGenerator(
            $this->configuration
        );
        $this->metas         = $metas ?? new Metas($this->configuration);

        $this->reset();
    }

    public function reset(): void
    {
        $this->titleLetters      = [];
        $this->currentTitleLevel = 0;
        $this->levels            = [];
        $this->counters          = [];

        for ($level = 0; $level < 16; $level++) {
            $this->levels[$level]   = 1;
            $this->counters[$level] = 0;
        }
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function setMetas(Metas $metas): void
    {
        $this->metas = $metas;
    }

    public function getNodeFactory(): NodeFactory
    {
        return $this->configuration->getNodeFactory($this);
    }

    public function getTemplateRenderer(): TemplateRenderer
    {
        return $this->configuration->getTemplateRenderer();
    }

    public function registerTextRole(TextRole $textRole): void
    {
        $this->textRoles[$textRole->getName()] = $textRole;
        foreach ($textRole->getAliases() as $alias) {
            $this->textRoles[trim(strtolower($alias))] = $textRole;
        }
    }

    public function isReference(string $section): bool
    {
        return ($this->textRoles[$section] ?? null) instanceof ReferenceRole;
    }

    public function getTextRole(string $section): ?TextRole
    {
        if (! isset($this->textRoles[$section])) {
            $this->addMissingTextRoleSectionError($section, 'text role');

            return null;
        }

        return $this->textRoles[$section];
    }

    /** @return TextRole[] */
    public function getSpecialTextRoles(): array
    {
        return array_filter($this->textRoles, static function (TextRole $value) {
            return $value->hasSpecialSyntax();
        });
    }

    /** @return TextRole[] */
    public function getRecursiveTextRoles(): array
    {
        return array_filter($this->textRoles, static function ($value) {
            return $value->hasRecursiveSyntax();
        });
    }

    public function addInvalidLink(InvalidLink $invalidLink): void
    {
        $this->invalidLinks[] = $invalidLink;
    }

    /** @return InvalidLink[] */
    public function getInvalidLinks(): array
    {
        return $this->invalidLinks;
    }

    /** @param mixed $value */
    public function setVariable(string $variable, $value): void
    {
        $this->variables[$variable] = $value;
    }

    public function createTitle(int $level): string
    {
        for ($currentLevel = 0; $currentLevel < 16; $currentLevel++) {
            if ($currentLevel <= $level) {
                continue;
            }

            $this->levels[$currentLevel]   = 1;
            $this->counters[$currentLevel] = 0;
        }

        $this->levels[$level] = 1;
        $this->counters[$level]++;
        $token = ['title'];

        for ($i = 1; $i <= $level; $i++) {
            $token[] = $this->counters[$i];
        }

        return implode('.', $token);
    }

    public function getNumber(int $level): int
    {
        return $this->levels[$level]++;
    }

    /**
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getVariable(string $variable, $default = null)
    {
        if (isset($this->variables[$variable])) {
            return $this->variables[$variable];
        }

        return $default;
    }

    /**
     * Adds a link target to the environment.
     *
     * https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#explicit-hyperlink-targets
     * https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#implicit-hyperlink-targets
     */
    public function setLinkTarget(LinkTarget $linkTarget): void
    {
        $name = $linkTarget->getName();
        if ($linkTarget->isAnonymous()) {
            $name = array_shift($this->anonymous);
        }

        $this->metas->setLinkTarget($name, $linkTarget);
    }

    public function resetAnonymousStack(): void
    {
        $this->anonymous = [];
    }

    public function pushAnonymous(string $name): void
    {
        $this->anonymous[] = trim(strtolower($name));
    }

    /** @return array<string, LinkTarget> */
    public function getLinkTargets(): array
    {
        return $this->getMetas()->getLinkTargets();
    }

    public function getLinkTarget(string $name, bool $relative = true): ?LinkTarget
    {
        return $this->metas->getLinkTarget($this->urlGenerator, $this->currentFileName, $name, $relative = true);
    }

    public function addDependency(string $dependency, bool $requiresResolving = false): void
    {
        if ($requiresResolving) {
            // a hack to avoid collisions between resolved and unresolved dependencies
            $dependencyName                            = 'UNRESOLVED__' . $dependency;
            $this->unresolvedDependencies[$dependency] = $dependencyName;
            // map the original dependency name to the one that will be stored
            $this->originalDependencyNames[$dependency] = $dependencyName;
        } else {
            // the dependency is already a filename, probably a :doc:
            // or from a toc-tree - change it to the canonical URL
            $canonicalDependency = $this->canonicalUrl($dependency);

            if ($canonicalDependency === null) {
                throw new InvalidArgumentException(sprintf(
                    'Could not get canonical url for dependency %s',
                    $dependency
                ));
            }

            $dependencyName = $canonicalDependency;
            // map the original dependency name to the one that will be stored
            $this->originalDependencyNames[$dependency] = $canonicalDependency;
        }

        if (in_array($dependencyName, $this->dependencies, true)) {
            return;
        }

        $this->dependencies[] = $dependencyName;
    }

    /** @return string[] */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function relativeUrl(?string $url): ?string
    {
        return $this->urlGenerator->relativeUrl($url, $this->currentFileName);
    }

    public function absoluteUrl(string $url): string
    {
        return $this->urlGenerator->absoluteUrl($this->getDirName(), $url);
    }

    public function canonicalUrl(string $url): ?string
    {
        return $this->urlGenerator->canonicalUrl($this->getDirName(), $url);
    }

    public function generateUrl(string $path): string
    {
        return $this->urlGenerator->generateUrl(
            $path,
            $this->currentFileName,
            $this->getDirName()
        );
    }

    public function getDirName(): string
    {
        $dirname = dirname($this->currentFileName);

        if ($dirname === '.') {
            return '';
        }

        return $dirname;
    }

    public function setCurrentFileName(string $filename): void
    {
        $this->currentFileName = $filename;
    }

    /**
     * Returns the currently-parsed filename.
     *
     * This is relative to the root source directory and without
     * the extension (e.g. "index" or "subdir/file")
     */
    public function getCurrentFileName(): string
    {
        return $this->currentFileName;
    }

    public function setCurrentDirectory(string $directory): void
    {
        $this->currentDirectory = $directory;
    }

    public function getCurrentDirectory(): string
    {
        return $this->currentDirectory;
    }

    public function absoluteRelativePath(string $url): string
    {
        return $this->currentDirectory . '/' . $this->getDirName() . '/' . $this->relativeUrl($url);
    }

    public function setTargetDirectory(string $directory): void
    {
        $this->targetDirectory = $directory;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    public function getUrl(): string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        return $this->currentFileName;
    }

    public function setUrl(string $url): void
    {
        if ($this->getDirName() !== '') {
            $url = $this->getDirName() . '/' . $url;
        }

        $this->url = $url;
    }

    public function getMetas(): Metas
    {
        return $this->metas;
    }

    public function getMetaEntry(): ?DocumentMetaData
    {
        return $this->metas->get($this->currentFileName);
    }

    public function getLevel(string $letter): int
    {
        foreach ($this->titleLetters as $level => $titleLetter) {
            if ($letter === $titleLetter) {
                return $level;
            }
        }

        $this->currentTitleLevel++;
        $this->titleLetters[$this->currentTitleLevel] = $letter;

        return $this->currentTitleLevel;
    }

    /** @return string[] */
    public function getTitleLetters(): array
    {
        return $this->titleLetters;
    }

    public static function slugify(string $text): string
    {
        return (new AsciiSlugger('en', []))->slug($text)->lower()->toString();
    }

    private function addMissingTextRoleSectionError(string $section, string $type): void
    {
        $this->errorManager->error(
            sprintf('Unknown ' . $type . ' section "%s"', $section),
            $this->getCurrentFileName()
        );
    }

    public function addInvalidReference(string $data): void
    {
        $this->addInvalidLink(new InvalidLink($data));

        if ($this->getMetaEntry() === null) {
            return;
        }

        $this->getMetaEntry()->removeDependency(
        // use the original name
            $this->originalDependencyNames[$data] ?? $data
        );
    }

    public function resolveDependency(string $data, ResolvedReference $resolvedReference): void
    {
        if (! isset($this->unresolvedDependencies[$data]) || $this->getMetaEntry() === null) {
            return;
        }

        $this->getMetaEntry()->resolveDependency(
            // use the unique, unresolved name
            $this->unresolvedDependencies[$data],
            $resolvedReference->getFile()
        );
    }
}
