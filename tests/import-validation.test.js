const assert = require("node:assert/strict");
const test = require("node:test");
const importer = require("../assets/A-import.js");

test("rejects a row with the wrong number of columns", () => {
  importer.cols = [["Email", "email", true], ["Age", "age", "N"]];
  assert.equal(importer.validate(["student@example.test"]), "Invalid column count");
});

test("rejects blank required values, including whitespace", () => {
  importer.cols = [["Email", "email", true], ["Age", "age", "N"]];
  assert.equal(importer.validate(["   ", "18"]), "Email - Cannot be empty");
});

test("rejects empty and non-numeric numeric values", () => {
  importer.cols = [["Email", "email", true], ["Age", "age", "N"]];
  assert.equal(importer.validate(["student@example.test", ""]), "Age - Invalid number");
  assert.equal(importer.validate(["student@example.test", "18x"]), "Age - Invalid number");
});

test("accepts valid values and optional empty columns", () => {
  importer.cols = [["Email", "email", true], ["Age", "age", "N"], ["Notes", "notes", false]];
  assert.equal(importer.validate(["student@example.test", "18", ""]), null);
});

test("validates email columns", () => {
  importer.cols = [["Email", "email", "E"]];
  assert.equal(importer.validate(["not-an-email"]), "Email - Invalid email");
  assert.equal(importer.validate(["student@example.test"]), null);
});
