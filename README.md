# Malini

**Malini** is an extensible content decoration and normalization library for WordPress.
It exposes a simple interface to *decorate* WordPress entities, like posts or archives, with all the information you may need in whatever format you're more comfortable with.

**Malini** introduces three basic concepts:

- **Decorators**: used to quickly declare the data you want to retrieve by decorating an entity;
- **Accessors**: used to define *how* the data will retrieved;
- **Filters**: used to alter values (similar to WordPress filters).

It also introduces two proxy-entities:

- **Malini\Post**: which includes a **WP_Post**;
- **Malini\Archive**: which is basically a collection of **Malini\Posts**.

## Development

Using **Malini** inside your code is quite easy.
To use it:

- Download and install the plugin to your WordPress website;
- Enable the plugin from the WP dashboard;
- Starts using it inside your theme or plugin.

Check the [wiki](https://github.com/caffeinalab/malini/wiki) to see what you can do with **Malini**.

## Updates

It is (*or will be*) possible to update **Malini** through WordPress as any other WordPress plugin.