Checklist:
1. Paste all folders inside this folder in front end project's `src` folder.
2. Register modules from `/store/modules` in `src/store/index.js`
3. Copy routes defined in `{model}-routes.js` files to `src/router/index.js` routes array
4. Update `src/components/NavigationDrawer.vue` `items` array with newly created routes
5. Copy translation keys from `{model}-translations.json` to every `src/i18n/{locale}.json` and add the translations
6. Go over form, table and filter components and make necessary adjustments, those components are likely not finished (e.g. by default filter components contain only one text filter, all table fields are strings, `item-text` prop for form autocompletes is set to `name`)
