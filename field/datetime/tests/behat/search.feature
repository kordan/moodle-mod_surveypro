@mod @mod_surveypro @surveyprofield @surveyprofield_datetime
Feature: Search using one and two datetime items
  In order to validate search form with one and two items
  As a student
  I search for submitted records.

  @javascript
  Scenario: Search using one and two datetime fields
    Given the following "courses" exist:
      | fullname                  | shortname   | category | numsections |
      | Test datetime search form | Search form | 0        | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Search form | editingteacher |
      | student1 | Search form | student        |
    And the following "activities" exist:
      | activity  | name                           | intro                  | course      |
      | surveypro | Date and time search form test | For searching purposes | Search form |
    And surveypro "Date and time search form test" has the following items:
      | type  | plugin   | settings                                                                                                                                                                                                                                                                    |
      | field | datetime | {"content":"Date and time of your last flight to Los Angeles?", "required":"1", "customnumber":"1", "defaultoption":"2", "hideinstructions":"1", "defaultvalue":"0", "downloadformat":"strftime01", "lowerbound":"157766400", "upperbound":"946684740", "insearchform":"1"} |
      | field | datetime | {"content":"Date and time of your last flight to Las Vegas?",   "required":"1", "customnumber":"2", "defaultoption":"2", "hideinstructions":"1", "defaultvalue":"0", "downloadformat":"strftime01", "lowerbound":"473385600", "upperbound":"1293839940"}                    |
    And I am on the "Date and time search form test" "surveypro activity" page logged in as student1

    # Add the first record
    And I press "New response"
    And I set the following fields to these values:
      | id_field_datetime_1_day    | 4    |
      | id_field_datetime_1_month  | 8    |
      | id_field_datetime_1_year   | 1982 |
      | id_field_datetime_1_hour   | 14   |
      | id_field_datetime_1_minute | 40   |
      | id_field_datetime_2_day    | 5    |
      | id_field_datetime_2_month  | 10   |
      | id_field_datetime_2_year   | 1985 |
      | id_field_datetime_2_hour   | 6    |
      | id_field_datetime_2_minute | 39   |
    And I press "Submit"

    # Add the second record
    And I press "New response"
    And I set the following fields to these values:
      | id_field_datetime_1_day    | 4    |
      | id_field_datetime_1_month  | 8    |
      | id_field_datetime_1_year   | 1982 |
      | id_field_datetime_1_hour   | 14   |
      | id_field_datetime_1_minute | 40   |
      | id_field_datetime_2_day    | 26   |
      | id_field_datetime_2_month  | 9    |
      | id_field_datetime_2_year   | 2005 |
      | id_field_datetime_2_hour   | 5    |
      | id_field_datetime_2_minute | 15   |
    And I press "Submit"

    # 1st search for submitted records
    Given I am on the "Date and time search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With only one field in the search form, "Ignore me" must not be in the form.
    And I should not see "*"
    And I set the following fields to these values:
      | id_field_datetime_1_day    | 4    |
      | id_field_datetime_1_month  | 8    |
      | id_field_datetime_1_year   | 1982 |
      | id_field_datetime_1_hour   | 14   |
      | id_field_datetime_1_minute | 40   |
    And I press "Search"
    Then I should see "2" submissions

    # Add the second search field
    And I am on the "Date and time search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_2')]" "xpath_element"

    # 2nd search for submitted records
    Given I am on the "Date and time search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the following fields to these values:
      | id_field_datetime_1_ignoreme | 0    |
      | id_field_datetime_1_day      | 4    |
      | id_field_datetime_1_month    | 8    |
      | id_field_datetime_1_year     | 1982 |
      | id_field_datetime_1_hour     | 14   |
      | id_field_datetime_1_minute   | 40   |
    And I press "Search"
    Then I should see "2" submissions

    # 3rd search for submitted records
    Given I am on the "Date and time search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the following fields to these values:
      | id_field_datetime_1_ignoreme | 0    |
      | id_field_datetime_1_day      | 4    |
      | id_field_datetime_1_month    | 8    |
      | id_field_datetime_1_year     | 1982 |
      | id_field_datetime_1_hour     | 14   |
      | id_field_datetime_1_minute   | 40   |
      | id_field_datetime_2_ignoreme | 0    |
      | id_field_datetime_2_day      | 26   |
      | id_field_datetime_2_month    | 9    |
      | id_field_datetime_2_year     | 2005 |
      | id_field_datetime_2_hour     | 5    |
      | id_field_datetime_2_minute   | 15   |
    And I press "Search"
    Then I should see "1" submissions
