Checklist:
1. Move `DummyTable.vue`, `DummyForm.vue` to components directory
2. Move `Dummys.vue`, `CreateDummy.vue`, `EditDummy.vue` to `views/dummys` directory
3. Move `dummy-service.js` to `api` directory
4. Move `dummys-module.js` to `store` directory. Register it in `store/index.js`
5. Copy routes defined in `router.js` to `router/index.js` routes array
6. Add `/dummys` link to `components/NavigationDrawer.vue` `items` array
7. Copy translation keys from `translations.json` to every `i18n/{locale}.json` and add the translations
8. Go over form and table fields and make adjustments as needed
