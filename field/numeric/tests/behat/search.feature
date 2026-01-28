@mod @mod_surveypro @surveyprofield @surveyprofield_numeric
Feature: Search using one and two numeric items
  In order to validate search form with one and two items
  As a student
  I search for submitted records.

  @javascript
  Scenario: Search using one and two numeric fields
    Given the following "courses" exist:
      | fullname                 | shortname   | category | numsections |
      | Test numeric search form | Search form | 0        | 1           |
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
      | surveypro | Numeric search form test | For searching purposes | Search form |
    And I am on the "Numeric search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    # Create a two items long surveypro
    And I set the field "typeplugin" to "Numeric"
    And I press "Add"
    And I expand all fieldsets

    Given I set the following fields to these values:
      | Content           | Type the best approximation of π you know |
      | Required          | 1                                         |
      | Question position | left                                      |
      | Element number    | 1.                                        |
      | Search form       | 1                                         |
      | Signed value      | 1                                         |
      | Decimal positions | 2                                         |
      | Minimum value     | 3                                         |
      | Maximum value     | 4                                         |
    And I press "Add"

    And I set the field "typeplugin" to "Numeric"
    And I press "Add"
    And I expand all fieldsets

    Given I set the following fields to these values:
      | Content           | Type the best approximation of Nepero's constant you know |
      | Required          | 1                                                         |
      | Question position | left                                                      |
      | Element number    | 2.                                                        |
      | Signed value      | 1                                                         |
      | Decimal positions | 2                                                         |
      | Minimum value     | 2                                                         |
      | Maximum value     | 3                                                         |
    And I press "Add"

    And I am on the "Numeric search form test" "mod_surveypro > Surveypro from secondary navigation" page logged in as student1

    # Add the first record
    And I press "New response"
    And I set the field "Type the best approximation of π you know" to "3.1"
    And I set the field "Type the best approximation of Nepero's constant you know" to "2.7"
    And I press "Submit"

    # Add the second record
    And I press "New response"
    And I set the field "Type the best approximation of π you know" to "3.1"
    And I set the field "Type the best approximation of Nepero's constant you know" to "2.71"
    And I press "Submit"

    # 1st search for submitted records
    Given I am on the "Numeric search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With only one field in the search form, "Ignore me" must not be in the form.
    And I should not see "*"

    And I set the field "id_field_numeric_1" to "3.1"
    And I press "Search"
    Then I should see "2" submissions

    # Add the second search field
    And I am on the "Numeric search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_2')]" "xpath_element"

    # 2nd search for submitted records
    Given I am on the "Numeric search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the field "id_field_numeric_2_ignoreme" to "0"
    And I set the field "id_field_numeric_2" to "2.7"
    And I press "Search"
    Then I should see "1" submissions

    # 3rd search for submitted records
    Given I am on the "Numeric search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the field "id_field_numeric_1_ignoreme" to "0"
    And I set the field "id_field_numeric_2_ignoreme" to "0"
    And I set the field "id_field_numeric_1" to "3.1"
    And I set the field "id_field_numeric_2" to "2.7"
    And I press "Search"
    Then I should see "1" submissions
