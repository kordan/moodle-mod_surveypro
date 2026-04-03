@mod @mod_surveypro @surveyprofield @surveyprofield_autofill
Feature: Search using one and two autofill items
  In order to validate search form with one and two items
  As a student
  I search for submitted records.

  @javascript
  Scenario: Search using one and two autofill fields
    Given the following "courses" exist:
      | fullname                  | shortname   | category | numsections |
      | Test autofill search form | Search form | 0        | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Search form | editingteacher |
      | student1 | Search form | student        |
    And the following "activities" exist:
      | activity  | name                      | intro                  | course      |
      | surveypro | Autofill search form test | For searching purposes | Search form |
    And surveypro "Autofill search form test" has the following items:
      | type  | plugin   | options                                                                        |
      | field | autofill | {"content":"Your first name", "element01":"userfirstname", "insearchform":"1"} |
      | field | autofill | {"content":"Your last name", "element01":"userlastname"}                       |
    And I am on the "Autofill search form test" "surveypro activity" page logged in as student1

    # Add the first record
    And I press "New response"
    And I press "Submit"

    # Add the second record
    And I press "New response"
    And I press "Submit"

    # 1st search for submitted records
    Given I am on the "Autofill search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With only one field in the search form, "Ignore me" must not be in the form.
    And I should not see "*"

    And I set the field "id_field_autofill_1" to "Student1"
    And I press "Search"
    Then I should see "2" submissions

    # Add the second search field
    And I am on the "Autofill search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_2')]" "xpath_element"

    # 2nd search for submitted records
    Given I am on the "Autofill search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the field "id_field_autofill_1_ignoreme" to "0"
    And I set the field "id_field_autofill_1" to "Student1"
    And I set the field "id_field_autofill_2_ignoreme" to "0"
    And I set the field "id_field_autofill_2" to "user1"
    And I press "Search"
    Then I should see "2" submissions
