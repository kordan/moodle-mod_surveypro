@mod @mod_surveypro @surveyprofield @surveyprofield_integer
Feature: Search using one and two integer items
  In order to validate search form with one and two items
  As a student
  I search for submitted records.

  @javascript
  Scenario: Search using one and two integer fields
    Given the following "courses" exist:
      | fullname                 | shortname   | category | numsections |
      | Test integer search form | Search form | 0        | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Search form | editingteacher |
      | student1 | Search form | student        |
    And the following "activities" exist:
      | activity  | name                     | intro                  | course      |
      | surveypro | Integer search form test | For searching purposes | Search form |
    And I am on the "Integer search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    # Create a two items long surveypro
    And I set the field "typeplugin" to "Integer (small)"
    And I press "Add"
    And I expand all fieldsets

    Given I set the following fields to these values:
      | Content           | How many people does your family counts? |
      | Required          | 1                                        |
      | Indent            | 0                                        |
      | Search form       | 1                                        |
      | Question position | left                                     |
      | Element number    | 1                                        |
    And I press "Add"

    And I set the field "typeplugin" to "Integer (small)"
    And I press "Add"
    And I expand all fieldsets

    Given I set the following fields to these values:
      | Content           | How many people does your parents family counts? |
      | Required          | 1                                                |
      | Indent            | 0                                                |
      | Question position | left                                             |
      | Element number    | 2                                                |
    And I press "Add"

    And I am on the "Integer search form test" "mod_surveypro > Surveypro from secondary navigation" page logged in as student1

    # Add the first record
    And I press "New response"
    And I set the field "id_field_integer_1" to "4"
    And I set the field "id_field_integer_2" to "2"
    And I press "Submit"

    # Add the second record
    And I press "New response"
    And I set the field "id_field_integer_1" to "2"
    And I set the field "id_field_integer_2" to "2"
    And I press "Submit"

    # 1st search for submitted records
    Given I am on the "Integer search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With only one field in the search form, "Ignore me" must not be in the form.
    Then the "id_field_integer_1" select box should not contain "*"

    And I set the following fields to these values:
      | id_field_integer_1 | 2 |
    And I press "Search"
    Then I should see "1" submissions

    # Add the second search field
    And I am on the "Integer search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_2')]" "xpath_element"

    # 2nd search for submitted records
    Given I am on the "Integer search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    Then the "id_field_integer_1" select box should contain "*"
    Then the "id_field_integer_2" select box should contain "*"

    And I set the following fields to these values:
      | id_field_integer_2 | 2 |
    And I press "Search"
    Then I should see "2" submissions

    # 3rd search for submitted records
    Given I am on the "Integer search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the following fields to these values:
      | id_field_integer_1 | 4 |
      | id_field_integer_2 | 2 |
    And I press "Search"
    Then I should see "1" submissions
