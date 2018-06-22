<!-- https://markdown-it.github.io/ -->

# Startit Theme

Un progetto [Mekit](http://www.mekit.it).

Sotto tema di Bootstrap di partenza.

Creato sulla versione del tema Drupal Bootstrap 8.x-3.3

## Utility

### Material Icons

Utilizzabili con questo markup: `<i class="material-icons">done</i>`

Oppure con il mixin `.materia-icons('done');`

Esempio:

``` css
&:before{
 .material-icons('done');
}
```

### Image with content overlay

Utilizzabile con questo markup di esempio.

`less/base/mekit.less`

``` twig
<a href="{{ url }}" title="{{ content.title.0 }}">
  <span class="wrapper-img-over">
    {{ content.field_image.0 }}
    <span class="wrapper-over">
      <span class="wrapper-over-content">
        ...
        <span class="foo">{{ content.title.0 }}</span>
        ...
      </span>
    </span>
  </span>
</a>
```
