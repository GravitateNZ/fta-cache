# fta-cache
Simple twig extension to allow templates to set caching headers

Install with composer in the usual fashion

`composer install gravitatenz/fta-cache`


Add the event listener to your services

```yaml
services:
    GravitateNZ\fta\cache\Event\CacheControlListener: ~
    GravitateNZ\fta\cache\Twig\CacheControlExtension: ~
```


Then drop in your twig

```html
{% do setPrivate() %}
{% do setPublic() %}
{% do setMaxAge(1000) %}
{% do doNotCache() %}
```

or

```html
{{ do setPrivate() }}
{{ do setPublic() }}
{{ do setMaxAge(1000) }}
{{ do doNotCache() }}
```

This can also be injected into a controller etc, and the listener and used directly. 

All of these will defer to the internal Symfomny session logic, if you are running a session, except for the `doNotCache` this will turn that off and force the headers cache-control and surrogate-control headers to be set to `max-age=0, nostore, private` 
