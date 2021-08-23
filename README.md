# fta-cache
Simple twig extension to allow templates to set caching headers

Install with composer in the usual fashion

`composer install gravitatenz/fta-cache`

Then drop in your twig

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

All of these will defer to the internal Symfomny session logic, if you are running a session, except for the `doNotCache` this will turn that off and force the headers cache-control and surrogate-control headers to be set to `max-age=0, nostore, private` 
