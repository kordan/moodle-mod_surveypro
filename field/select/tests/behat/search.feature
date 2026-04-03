@mod @mod_surveypro @surveyprofield @surveyprofield_select
Feature: Search using one and two select items
  In order to validate search form with one and two items
  As a student
  I search for submitted records.

  @javascript
  Scenario: Search using one and two select fields
    Given the following "courses" exist:
      | fullname                | shortname   | category | numsections |
      | Test select search form | Search form | 0        | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Search form | editingteacher |
      | student1 | Search form | student        |
    And the following "activities" exist:
      | activity  | name                    | intro                  | course      |
      | surveypro | Select search form test | For searching purposes | Search form |
    And surveypro "Select search form test" has the following items:
      | type  | plugin | options                                                                                                                                                          |
      | field | select | {"content":"Which summer holidays place do you prefer?", "required":"1", "customnumber":"1", "options":"sea\nmountain\nlake\nhills\ndesert", "insearchform":"1"} |
      | field | select | {"content":"Which winter holidays place do you prefer?", "required":"1", "customnumber":"2", "options":"sea\nmountain\nlake\nhills\ndesert"}                     |
    When I am on the "Select search form test" "surveypro activity" page logged in as student1

    # Add the first record
    And I press "New response"
    And I set the following fields to these values:
      | id_field_select_1 | sea      |
      | id_field_select_2 | mountain |
    And I press "Submit"

    # Add the second record
    And I press "New response"
    And I set the following fields to these values:
      | id_field_select_1 | sea   |
      | id_field_select_2 | hills |
    And I press "Submit"

    # 1st search for submitted records
    Given I am on the "Select search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With only one field in the search form, "Ignore me" must not be in the form.
    Then the "id_field_select_1" select box should not contain "*"

    And I set the field "id_field_select_1" to "sea"
    And I press "Search"
    Then I should see "2" submissions

    # Add the second search field
    And I am on the "Select search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_2')]" "xpath_element"

    # 2nd search for submitted records
    Given I am on the "Select search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    Then the "id_field_select_1" select box should contain "*"
    Then the "id_field_select_2" select box should contain "*"

    And I set the field "id_field_select_2" to "hills"
    And I press "Search"
    Then I should see "1" submissions

    # 3rd search for submitted records
    Given I am on the "Select search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the field "id_field_select_1" to "sea"
    And I set the field "id_field_select_2" to "hills"
    And I press "Search"
    Then I should see "1" submissions
