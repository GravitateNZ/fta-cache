# fta-cache
Simple twig extension to allow templates to set caching headers


```html
{% do setPrivate() %}
{% do setPublic() %}
{% do setMaxAge(1000) %}
```

or
```html
{{ setPrivate() }}
```
