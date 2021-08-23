# fta-cache
Simple twig extension to allow templates to set caching headers


```html
{% do setPrivate() %}
{% do setPublic() %}
{% do setMaxAge(1000) %}
{% do doNotCache() %}
```

or
```html
{{ setPrivate() }}
```
