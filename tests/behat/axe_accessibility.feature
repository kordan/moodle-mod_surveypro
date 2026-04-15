@mod @mod_surveypro
Feature: Execute accessibility tests
  In order to validate the accessibility of Surveypro pages
  As student1 and admin
  I reach each page and I verify it.

  @javascript @accessibility
  Scenario: Check surveypro accessibility
    Given the following "courses" exist:
      | fullname                             | shortname               | category |
      | Surveypro landing page accessibility | Surveypro accessibility | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                   | role    |
      | student1 |  Surveypro accessibility | student |
    And the following "activities" exist:
      | activity  | name               | intro              | course                  |
      | surveypro | Accessibility test | Accessibility test | Surveypro accessibility |
    And surveypro "Accessibility test" has the following items:
      | type   | plugin      | settings                                                                             |
      | field  | age         | {"insearchform":"1"}                                                                 |
      | field  | fileupload  | {"indent":"1"}                                                                       |
      | field  | autofill    | {"reserved":"1"}                                                                     |
      | field  | boolean     | {"defaultoption":"3"}                                                                |
      | field  | checkbox    | {"defaultoption":"3", "indent":"2", "parentid":"@@itemid_04@@", "parentcontent":"1"} |
      | field  | shortdate   | {"defaultoption":"3"}                                                                |
      | field  | date        | {"defaultoption":"3"}                                                                |
      | field  | date        | {"hidden":"1"}                                                                       |
      | field  | date        | {"hidden":"1"}                                                                       |
      | field  | datetime    | {"defaultoption":"3"}                                                                |
      | format | pagebreak   |                                                                                      |
      | field  | integer     |                                                                                      |
      | field  | multiselect | {"noanswerdefault":"3"}                                                              |
      | field  | numeric     |                                                                                      |
      | field  | radiobutton | {"defaultoption":"3"}                                                                |
      | format | fieldset    |                                                                                      |
      | field  | rate        | {"defaultoption":"3"}                                                                |
      | format | fieldsetend |                                                                                      |
      | field  | recurrence  | {"defaultoption":"3"}                                                                |
      | field  | select      | {"defaultoption":"3"}                                                                |
      | field  | textarea    |                                                                                      |
      | field  | character   |                                                                                      |
      | field  | time        | {"defaultoption":"3"}                                                                |
      | format | label       |                                                                                      |

    # Master templates - manage
    When I am on the "Accessibility test" "mod_surveypro > Master templates from secondary navigation" page logged in as admin
    Then the page should meet accessibility standards

    # Master templates - apply
    When I select "Apply" from the "jump" singleselect
    Then the page should meet accessibility standards

    # User templates - manage
    When I am on the "Accessibility test" "mod_surveypro > User templates from secondary navigation" page logged in as admin
    Then the page should meet accessibility standards

    # User templates - save
    When I select "Save" from the "jump" singleselect
    Then the page should meet accessibility standards
    And I press "Save"

    # User templates - import
    When I select "Import" from the "jump" singleselect
    Then the page should meet accessibility standards

    # User templates - apply
    When I select "Apply" from the "jump" singleselect
    Then the page should meet accessibility standards

    # Tools - export
    When I am on the "Accessibility test" "mod_surveypro > Tools from secondary navigation" page logged in as admin
    Then the page should meet accessibility standards

    # Tools - import
    When I select "Import" from the "jump" singleselect
    Then the page should meet accessibility standards

    # Reports - attachment overview
    When I am on the "Accessibility test" "mod_surveypro > Reports from secondary navigation" page logged in as admin
    Then the page should meet accessibility standards

    # Reports - frequency distribution
    When I select "Frequency distribution" from the "jump" singleselect
    Then the page should meet accessibility standards

    # Reports - late users
    When I select "Late users" from the "jump" singleselect
    Then the page should meet accessibility standards

    # Reports - responses per user
    When I select "Responses per user" from the "jump" singleselect
    Then the page should meet accessibility standards

    # Reports - users per count of responses
    When I select "Users per count of responses" from the "jump" singleselect
    Then the page should meet accessibility standards

    # Layout - elements
    When I am on the "Accessibility test" "mod_surveypro > Layout from secondary navigation" page logged in as admin
    Then the page should meet accessibility standards

    # Layout - preview
    When I select "Preview" from the "jump" singleselect
    Then the page should meet accessibility standards
    And I press "Next page >>"
    Then the page should meet accessibility standards

    # Surveypro - dashboard as student
    When I am on the "Accessibility test" "surveypro activity" page logged in as student1
    Then the page should meet accessibility standards

    When I press "New response"
    And I set the following fields to these values:
      | id_field_age_1_year  | 3 |
      | id_field_age_1_month | 6 |
    And I press "Next page >>"
    And I set the field "id_field_integer_12" to "3"
    And I press "Submit"
    And I press "Continue to responses list"

    # Surveypro - responses as student
    Then the page should meet accessibility standards

    When I click action "Read only" on item 1
    Then the page should meet accessibility standards

    # Surveypro - search as student
    When I select "Search" from the "jump" singleselect
    Then the page should meet accessibility standards

    And I set the following fields to these values:
      | id_field_age_1_year  | 4 |
      | id_field_age_1_month | 6 |
    And I press "Search"
    Then the page should meet accessibility standards

    # ci devo mettere le relazioni
