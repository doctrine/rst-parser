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
