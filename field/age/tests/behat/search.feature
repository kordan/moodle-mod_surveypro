@mod @mod_surveypro @surveyprofield @surveyprofield_age
Feature: Search using one and two age items
  In order to validate search form with one and two items
  As a student
  I search for submitted records.

  @javascript
  Scenario: Search using one and two age fields
    Given the following "courses" exist:
      | fullname             | shortname   | category | numsections |
      | Test age search form | Search form | 0        | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Search form | editingteacher |
      | student1 | Search form | student        |
    And the following "activities" exist:
      | activity  | name                 | intro                  | course      |
      | surveypro | Age search form test | For searching purposes | Search form |
    And surveypro "Age search form test" has the following items:
      | type  | plugin  | settings                                                                                                                                                                                                                                                                          |
      | field | age     | {"content":"How old were you when you started cycling?", "required":"1", "indent":"1", "position":"0", "customnumber":"1", "hideinstructions":"1", "defaultoption":"2", "defaultvalue":"-2148552000", "lowerbound":"-2117016000", "upperbound":"-1706788800", "insearchform":"1"} |
      | field | age     | {"content":"How old were you when you got married?",     "required":"1", "indent":"1", "position":"0", "customnumber":"2", "hideinstructions":"1", "defaultoption":"2", "defaultvalue":"-2148552000", "lowerbound":"-1580558400", "upperbound":"533822400"}                       |
    When I am on the "Age search form test" "surveypro activity" page logged in as student1

    # Add the first record
    And I press "New response"
    And I set the following fields to these values:
      | id_field_age_1_year  | 3  |
      | id_field_age_1_month | 6  |
      | id_field_age_2_year  | 28 |
      | id_field_age_2_month | 8  |
    And I press "Submit"

    # Add the second record
    And I press "New response"
    And I set the following fields to these values:
      | id_field_age_1_year  | 3  |
      | id_field_age_1_month | 6  |
      | id_field_age_2_year  | 33 |
      | id_field_age_2_month | 11 |
    And I press "Submit"

    # 1st search for submitted records
    Given I am on the "Age search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With only one field in the search form, "Ignore me" must not be in the form.
    And I should not see "*"
    And I set the following fields to these values:
      | id_field_age_1_year  | 3 |
      | id_field_age_1_month | 6 |
    And I press "Search"
    Then I should see "2" submissions

    # Add the second search field
    And I am on the "Age search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_2')]" "xpath_element"

    # 2nd search for submitted records
    Given I am on the "Age search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the following fields to these values:
      | id_field_age_1_ignoreme | 0 |
      | id_field_age_1_year     | 3 |
      | id_field_age_1_month    | 6 |
    And I press "Search"
    Then I should see "2" submissions

    # 3rd search for submitted records
    Given I am on the "Age search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the following fields to these values:
      | id_field_age_1_ignoreme | 0  |
      | id_field_age_1_year     | 3  |
      | id_field_age_1_month    | 6  |
      | id_field_age_2_ignoreme | 0  |
      | id_field_age_2_year     | 28 |
      | id_field_age_2_month    | 8  |
    And I press "Search"
    Then I should see "1" submissions
