# Upgrade to 0.6

## TitleNode requires an id

The TitleNode constructor is changed to require an ID as 4th argument.
Similarly, `Doctrine\RST\NodeFactory::createTitleNode()` has been
updated with an ID as 4th argument.

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
