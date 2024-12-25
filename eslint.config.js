import globals from "globals";
import pluginJs from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import eslintConfigPrettier from "eslint-config-prettier";

export default [
  { files: ["**/*.{js,mjs,cjs,vue}"] },
  { languageOptions: { globals: globals.browser } },
  pluginJs.configs.recommended,
  ...pluginVue.configs[("flat/essential", "flat/strongly-recommended")],
  {
    rules: {
      "max-len": ["error", { code: 120 }],
    },
  },
  eslintConfigPrettier,
];
