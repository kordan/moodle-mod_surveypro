@mod @mod_surveypro @surveyprofield @surveyprofield_age
Feature: Use reserved elements
  In order to test reserved elements are only seen by teacher and not students
  As a teacher and student
  I add two items with different availability and go to fill the corresponding survey and edit it

  @javascript
  Scenario: Use reserved elements
    Given the following "courses" exist:
      | fullname          | shortname         | category | groupmode |
      | Reserved elements | Reserved elements | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
      | student1 | Student   | 1        | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course            | role           |
      | teacher1 | Reserved elements | editingteacher |
      | student1 | Reserved elements | student        |
    And the following "activities" exist:
      | activity  | name                  | intro                    | course            |
      | surveypro | Reserved element test | To test reserved element | Reserved elements |
    And surveypro "Reserved element test" has the following items:
      | type  | plugin | settings                                                                                                                                                                                                                                               |
      | field | age    | {"content":"First age item",  "required":"1", "indent":"0", "position":"0", "customnumber":"1", "hideinstructions":"1", "defaultoption":"2", "defaultvalue":"-2148552000", "lowerbound":"-2148552000", "upperbound":"-1193918400", "insearchform":"1"} |
      | field | age    | {"content":"Second age item", "required":"1", "indent":"0", "position":"0", "customnumber":"2", "hideinstructions":"1", "defaultoption":"2", "defaultvalue":"-2148552000", "lowerbound":"-2148552000", "upperbound":"-1193918400", "reserved":"1"}    |

    # test the user sees only the first age item
    And I am on the "Reserved element test" "surveypro activity" page logged in as student1
    And I select "Responses" from the "jump" singleselect
    And I press "New response"

    Then I should see "1 First age item"
    Then I should not see ": Second age item"

    # user submit a surveypro
    And I set the following fields to these values:
      | id_field_age_1_year  | 8 |
      | id_field_age_1_month | 2 |
    And I press "Submit"

    And I log out

    # test the teacher sees the first and the second age items both
    And I am on the "Reserved element test" "surveypro activity" page logged in as teacher1
    And I select "Responses" from the "jump" singleselect
    And I click action "Edit" on item 1

    Then I should see "1 First age item"
    Then I should see "2 Second age item"

    And I set the following fields to these values:
      | id_field_age_2_year  | 24 |
      | id_field_age_2_month | 6  |
    And I press "Submit"
