# Twig Attributes

Twig Attributes allows developers to set HTML attributes (such as classes or an id) in a parent Twig template to elements in a child template, eliminating the need to create a template override or implement a preprocess hook just to add an attribute. It is particularly useful when working with fields that do not by default support attributes on certain HTML elements, such as links. Twig Attributes includes specific support for a number of templates provided by Drupal core, including ones used for image fields, responsive image fields, and links.

Attributes are added using a Twig filter (`add_attr`) on the element to be rendered, with the following arguments:

- **Key:** The key of the property element to which the attributes should be added. If the first character is not a hash ("#"), one will automatically be prepended.
- **Attributes:** The attributes to add. This should be an array of key/value pairs. The key is the name of the attribute to add and the value, which can either be a string or an array, is the attribute's value.
- **Add to Children:** By default, attributes are added to the child elements (non-property elements that don't begin with "#") of the renderable array. Pass `false` as the third argument to alter this behavior and set the attributes on the parent element.
- **Override:** By default, if an attribute value is an array, new values are merged with existing ones. Pass `true` as the fourth argument to alter this behavior and override any existing value with a new one.

## Examples

To add a class to an `<img>` tag when rendering an image field:

    {{ content.field_image|add_attr('image_attributes', {class: ['custom-class']}) }}

To add an ID to an `<a>` tag when rendering a link field:

    {{ content.field_link|add_attr('link_attributes', {id: 'custom-id'}) }}

Filters can be chained to set attributes on multiple elements in a template:

    {{ content.field_image
      |add_attr('image_attributes', {class: ['custom-image-class']})
      |add_attr('link_attributes', {class: ['custom-link-class']})
    }}

`with_attr` is available as an alias to `add_attr`.
