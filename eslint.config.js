// ESLint configuration.
//
// This is "flat config": the file exports an array, and each object in it
// applies to whichever files it names. Later objects override earlier ones,
// so the order matters. Older projects you find online use a .eslintrc.json
// instead -- that format was removed in ESLint 10 and no longer works.
//
//   npm run lint        report problems
//   npm run lint:fix    fix the ones that can be fixed automatically
//
// Formatting is NOT handled here. Prettier does that, with `npm run format`.
// Keeping them apart means a lint error always signals a real problem with
// the code, rather than a misplaced space.

import js from "@eslint/js";
import globals from "globals";
import unicorn from "eslint-plugin-unicorn";
import prettierCompat from "eslint-config-prettier";

export default [
  {
    files: ["www/public/js/**/*.js"],

    languageOptions: {
      ecmaVersion: "latest",
      sourceType: "module",

      // Tells ESLint that things like document, window, fetch and console
      // exist. Without this it flags every one of them as undefined.
      globals: globals.browser,
    },

    plugins: { unicorn },

    rules: {
      // ESLint's own recommended set: the rules that catch outright
      // mistakes, like unused variables and unreachable code.
      ...js.configs.recommended.rules,

      "no-console": "off", // console.log is a legitimate debugging tool
      "no-var": "error", // let and const only
      eqeqeq: "warn", // === rather than ==
      "no-debugger": "warn", // fine while working, not in what you hand in

      // A hand-picked set from eslint-plugin-unicorn.
      //
      // The plugin's own "recommended" set is far larger and much of it is
      // stylistic -- it will tell you to rename files and rewrite loops that
      // are perfectly fine, which trains people to ignore lint output. What
      // follows is limited to rules that catch real bugs or steer you toward
      // the modern DOM and fetch APIs this course teaches.

      // DOM
      "unicorn/prefer-query-selector": "warn", // querySelector over getElementById etc.
      "unicorn/prefer-add-event-listener": "error", // not onclick = ...
      "unicorn/prefer-dom-node-append": "warn", // append over appendChild
      "unicorn/prefer-dom-node-remove": "warn", // remove over removeChild
      "unicorn/dom-node-dataset": "warn", // dataset over getAttribute("data-*")
      "unicorn/prefer-modern-dom-apis": "warn",
      "unicorn/no-document-cookie": "error", // use the Cookie Store API

      // fetch and promises
      "unicorn/no-invalid-fetch-options": "error", // e.g. a body on a GET
      "unicorn/no-await-in-promise-methods": "error", // defeats Promise.all
      "unicorn/no-single-promise-in-promise-methods": "error",

      // Everyday correctness
      "unicorn/no-instanceof-builtins": "error", // instanceof Array is a trap
      "unicorn/prefer-array-find": "warn", // find over filter()[0]
      "unicorn/prefer-includes": "warn", // includes over indexOf !== -1
      "unicorn/error-message": "error", // throw new Error("say why")
      "unicorn/throw-new-error": "error", // throw new Error, not Error()
    },
  },

  // Last, so it wins: switches off every rule that would argue with Prettier
  // about formatting.
  prettierCompat,
];
