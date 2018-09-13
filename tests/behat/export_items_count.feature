@mod @mod_surveypro
Feature: adding a fileupload item, two more item are added to export type menu
  In order to test that adding a fileupload item, two more item are added to export type menu
  As teacher1
  I fill build a surveypro without and, later, with fileupload and go to verify export type menu items

  @javascript
  Scenario: count the number of items of export type menu
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
      | activity  | name             | intro                     | course           | idnumber   |
      | surveypro | Export-item test | To check export type menu | Count menu items | surveypro1 |
    And surveypro "Export-item test" contains the following items:
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
    And I log in as "teacher1"
    And I am on "Count export type menu items" course homepage
    And I follow "Export-item test"
    And I navigate to "Survey > Export" in current page administration

    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '1')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '2')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '3')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '4')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '5')]" "xpath_element" should exist

    And I follow "Layout"
    And I follow "delete_item_2"
    And I press "Yes"

    And I navigate to "Survey > Export" in current page administration

    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '1')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '2')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '3')]" "xpath_element" should exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '4')]" "xpath_element" should not exist
    Then "//select[contains(@id, 'id_downloadtype')]//option[contains(@value, '5')]" "xpath_element" should not exist
