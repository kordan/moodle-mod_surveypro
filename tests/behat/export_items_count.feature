@mod @mod_surveypro
Feature: Verify export type menu after adding a fileupload item
  In order to test that adding a fileupload item, two more item are added to export type menu
  As teacher1
  I fill build a surveypro without and, later, with fileupload and go to verify export type menu items

  @javascript
  Scenario: Count the number of items of the "Download file type" menu
    Given the following "courses" exist:
      | fullname                     | shortname        | category | groupmode |
      | Count export type menu items | Count menu items | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course           | role           |
      | teacher1 | Count menu items | editingteacher |
    And the following "activities" exist:
      | activity  | name             | intro                     | course           |
      | surveypro | Export-item test | To check export type menu | Count menu items |
    And surveypro "Export-item test" has the following items:
      | type   | plugin      |
      | field  | age         |
      | field  | fileupload  |
      | field  | autofill    |
      | field  | boolean     |
      | field  | checkbox    |
      | field  | shortdate   |
      | field  | date        |
      | field  | datetime    |
      | format | pagebreak   |
      | field  | integer     |
      | field  | multiselect |
      | field  | numeric     |
      | field  | radiobutton |
      | format | fieldset    |
      | field  | rate        |
      | format | fieldsetend |
      | field  | recurrence  |
      | field  | select      |
      | field  | textarea    |
      | field  | character   |
      | field  | time        |
      | format | label       |
    And I am on the "Export-item test" "mod_surveypro > Tools from secondary navigation" page logged in as teacher1

    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '1')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '2')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '3')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '4')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '5')]" "xpath_element" should exist

    And I am on the "Export-item test" "mod_surveypro > Layout from secondary navigation" page
    And I follow "delete_item_2"
    And I press "Yes"
    And I am on the "Export-item test" "mod_surveypro > Tools from secondary navigation" page

    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '1')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '2')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '3')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '4')]" "xpath_element" should not exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '5')]" "xpath_element" should not exist
