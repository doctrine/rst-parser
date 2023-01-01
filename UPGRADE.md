# Upgrade to 0.6

## Refactored reference handling

* References are now treated as text roles. They can extend 
  ``Doctrine\RST\TextRoles\ReferenceRole`` for convenience. 
* ``Doctrine\RST\References\Reference`` has been removed. Use a text role instead.

## Added methods to NodeFactory

* Added `NodeFactory::createFieldListNode()`.

## Removed the class Kernel

* Directives and TextRoles can now be registered by adding a DirectiveFactory to
  the configuration.
* Use the `PostBuilderInitEvent` instead of overriding `Kernel::initBuilder()`.
* Use the `PostProcessFileEvent` instead of overriding `Kernel::postParse()`.

## Renamed links to link targets

The library has been updated to use "link target" instead of "link":

* Renamed `Environment::setLink()`, `Environment::getLink()` and
  `Environment::getLinks()` to `Environment::setLinkTarget()`,
  `Environment::getLinkTarget()` and `Environment::getLinkTargets()`
* Renamed `Metas::findLinkMetaEntry()` and `Metas::doesLinkExist()` to
  `Metas::findLinkTargetMetaEntry()` and `Metas::doesLinkTargetExist()`.
* Renamed `DocumentParser::parseLink()` to `DocumentParser::parseLinkTarget()`.
* Renamed `LineDataParser::parseLink()` and `LineDataParser::createLink()` to
  `LineDataParser::parseLinkTarget()` and `LineDataParser::createLinkTarget()`.

## Refactored Directive Options

* Removed `DirectiveOption` in favor of `FieldOption` (with a different signature)
* Removed `LineDataParser::parseDirectiveOption()` in favor of `LineDataParser::parseFieldOption()`

# Upgrade to 0.5

## Property visibility changed from protected to private

Some directives and node classes had protected visibility for their properties.
That has now been changed to private.

## Final classes by default

Many classes have been made final because they were never actually extended.

## Error handling

The `Doctrine\RST\Environment::addError()` and `Doctrine\RST\Environment::addWarning()`
have been removed.

The `Doctrine\RST\ErrorManager::error()` and `Doctrine\RST\ErrorManager::warning()`
have an updated signature to support file name and line numbers.

Method `Doctrine\RST\ErrorManager::getErrors()` will return a list of `Doctrine\RST\Error`
instead of a list of strings.

# Upgrade to 0.4

## Refactored List Rendering

Removed the `list.html.twig` template, override `enumerated-list.html.twig` and
`bullet-list.html.twig` instead.

In addition, removed `Doctrine\RST\Parser\HTML\Renderers\ListRenderer`,
`Doctrine\RST\Parser\LaTex\Renderers\ListRenderer` and
`Doctrine\RST\Parser\Renderers\ListNodeRenderer` in favor of `ListNodeRenderer`
classes for HTML and LaTex, which receive the new `ListNode`.

## Refactored List Parsing

Removed `Doctrine\RST\Parser\ListLine` in favor of `Doctrine\RST\Parser\ListItem`
and changed signature of `Doctrine\RST\Parser\LineChecker::isListLine()`.

# Upgrade to 0.3

## `DefinitionListTerm::$definitions` is a list of `Node`'s instead of `SpanNode`'s

If you define a custom `definition-list.html.twig`, no longer wrap the value in
a `<p>` element (the `.first` and `.last` classes are automatically added):

```diff
  <dl>
      {% for definitionListTerm in definitionList.terms %}
      <dt>{{ definitionListTerm.term.render()|raw }}</dt>
      <dd>
          {% for definition in definitionListTerm.definitions %}
-             <p>{{ definition.render()|raw }}</p>
+             {{ definition.render()|raw }}
          {% endfor %}
      </dd>
  </dl>
```
