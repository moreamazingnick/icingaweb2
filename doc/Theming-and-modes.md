# Theming and Modes

Welcome! This is a short guide how to adjust your module's LESS rules to be
compatible with Icinga Web 2 v2.10 and its brand new dark/light mode support.

## Introduction

Icinga Web 2 v2.10 has a new default style that differs greatly from previous
ones in terms of contrast. While previous versions of Icinga Web 2 had a light
default style, v2.10's is now dark.

This is the basis for the now also new light mode. Light mode rules are very
similar to what themes are. They get applied on top of the standard rules and
change or extend them.

## The Basics

Light mode support is established by using native CSS variables for colors.
An example how this looks like:

```less
.my-class {
  color: var(--text-color, @text-color);
}
```

You'll notice that there's a fallback defined. The fallback is what is used
while a user is in dark mode. The CSS variable on the other hand is active
while a user is in light mode.

In this case a global CSS and LESS variable defined by Icinga Web 2 is used.
This means you don't have to do anything else. If it's your own variable,
you'll have to also define its CSS version just like you do for your own
LESS variables.

Consider this example:

```less
@my-variable: black;

.my-box {
  background-color: var(--my-variable, @my-variable);
}
```

The CSS variable `--my-variable` is undefined. To define it, create a new file
`public/css/modes/light.less` in your module. Inside it, add the following:

```less
@light-variables: {
  --my-variable: white;
};
```

This creates a [detached ruleset](https://lesscss.org/features/#detached-rulesets-feature).
The name of the ruleset is important! It needs to be *light-variables*, otherwise
your variables are not defined in light mode.

From this point on, Icinga Web 2 will make sure the variables you defined in
`public/css/modes/light.less` will be active for users that are in light mode.

## Common Pitfalls

### Parametrized Mixins

Combining mixins that accept parameters and CSS variables can be a bit tricky.
You might need to [escape](https://lesscss.org/#escaping) the value and use
[variable interpolation](https://lesscss.org/features/#variables-feature-variable-interpolation)
when passing it to a mixin:

```less

.basic-list(@h-padding, @v-padding, @odd-bg, @even-bg) {
  ...
}

.my-list {
  .basic-list(.2em, .4em, ~"var(--gray-light, @{gray-light})", ~"var(--gray-lightest, @{gray-lightest})");
}
```

You can also use this when defining default values for the mixin:

```less
.basic-list(
  @h-padding: .2em,
  @v-padding: .4em,
  @odd-bg: ~"var(--gray-light, @{gray-light})",
  @even-bg: ~"var(--gray-lightest, @{gray-lightest})"
) {
  ...
}

.my-list {
  .basic-list();
}
```

### Functions

When applying functions like `fade` or `lighten` to variables, you have to
remember that this is only applied by LESS. The CSS variable cannot be adjusted
this way. You will have to apply the function in your light rules again:

```less
.my-button {
  background-color: var(--body-bg-color, fade(@body-bg-color, 50%));
}

@light-variables: {
  --body-bg-color: fade(black, 50%);
};
```

Did you note that the example uses a global variable of Icinga Web 2? This is
a rather bad example, but on purpose. Adjusting a global CSS variable, while
not in a theme, is discouraged. It also affects not only the background color
of `.my-button`, but also all other usages of `--body-bg-color` inside your
module.

In such a case, it is highly recommended to introduce your own version of it
and apply the function there instead:

```less
@my-button-bg-color: fade(@body-bg-color, 50%);

.my-button {
  background-color: var(--my-button-bg-color, @my-button-bg-color);
}

@light-variables: {
  --my-button-bg-color: fade(black, 50%);
};
```
