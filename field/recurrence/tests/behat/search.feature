@mod @mod_surveypro @surveyprofield @surveyprofield_recurrence
Feature: Search using one and two recurrence items
  In order to validate search form with one and two items
  As a student
  I search for submitted records.

  @javascript
  Scenario: Search using one and two recurrence fields
    Given the following "courses" exist:
      | fullname                    | shortname   | category | numsections |
      | Test recurrence search form | Search form | 0        | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Search form | editingteacher |
      | student1 | Search form | student        |
    And the following "activities" exist:
      | activity  | name                        | intro                  | course      |
      | surveypro | Recurrence search form test | For searching purposes | Search form |
    And surveypro "Recurrence search form test" has the following items:
      | type  | plugin     | settings                                                                                                                                                                                                                                             |
      | field | recurrence | {"content":"When do you usually celebrate your name-day?",        "required":"1", "customnumber":"1", "defaultoption":"2", "defaultvalue":"43200", "downloadformat":"strftime01", "lowerbound":"43200", "upperbound":"31492800", "insearchform":"1"} |
      | field | recurrence | {"content":"When do you celebrate your town's patron saint day?", "required":"1", "customnumber":"2", "defaultoption":"2", "defaultvalue":"43200", "downloadformat":"strftime01", "lowerbound":"43200", "upperbound":"31492800"}                     |
    When I am on the "Recurrence search form test" "surveypro activity" page logged in as student1

    # Add the first record
    And I press "New response"
    And I set the following fields to these values:
      | id_field_recurrence_1_day   | 8       |
      | id_field_recurrence_1_month | March   |
      | id_field_recurrence_2_day   | 12      |
      | id_field_recurrence_2_month | October |
    And I press "Submit"

    # Add the second record
    And I press "New response"
    And I set the following fields to these values:
      | id_field_recurrence_1_day   | 8      |
      | id_field_recurrence_1_month | March  |
      | id_field_recurrence_2_day   | 3      |
      | id_field_recurrence_2_month | August |
    And I press "Submit"

    # 1st search for submitted records
    Given I am on the "Recurrence search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With only one field in the search form, "Ignore me" must not be in the form.
    And I should not see "*"
    And I set the following fields to these values:
      | id_field_recurrence_1_day   | 8       |
      | id_field_recurrence_1_month | March   |
    And I press "Search"
    Then I should see "2" submissions

    # Add the second search field
    And I am on the "Recurrence search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_2')]" "xpath_element"

    # 2nd search for submitted records
    Given I am on the "Recurrence search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the following fields to these values:
      | id_field_recurrence_1_ignoreme | 0     |
      | id_field_recurrence_1_day      | 8     |
      | id_field_recurrence_1_month    | March |
    And I press "Search"
    Then I should see "2" submissions

    # 3rd search for submitted records
    Given I am on the "Recurrence search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the following fields to these values:
      | id_field_recurrence_1_ignoreme | 0      |
      | id_field_recurrence_1_day      | 8      |
      | id_field_recurrence_1_month    | March  |
      | id_field_recurrence_2_ignoreme | 0      |
      | id_field_recurrence_2_day      | 3      |
      | id_field_recurrence_2_month    | August |
    And I press "Search"
    Then I should see "1" submissions
