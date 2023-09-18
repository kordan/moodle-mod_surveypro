@mod @mod_surveypro @surveyprofield
Feature: test the use of boolean as parent item
  In order to test boolean as parent item
  As a teacher
  I create a parent-child relation and I evaluate the outcome when relation is fulfilled and not fulfilled

  @javascript
  Scenario: test boolean as parent
    Given the following "courses" exist:
      | fullname          | shortname         | category | groupmode |
      | Boolean as parent | Boolean as parent | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course            | role           |
      | teacher1 | Boolean as parent | editingteacher |
      | student1 | Boolean as parent | student        |
    And the following "activities" exist:
      | activity  | name                   | intro                  | newpageforchild | course            |
      | surveypro | Test boolean as parent | Test boolean as parent | 1               | Boolean as parent |
    And surveypro "Test boolean as parent" contains the following items:
      | type   | plugin  |
      | field  | boolean |
    And I am on the "Test boolean as parent" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    # add an boolean item
    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Write down your name     |
      | Parent element | Boolean [1]: Is it true? |
      | Parent content | 1                        |
    And I press "Add"

    And I log out

    # test the the child item correctly appear or not appear
    When I am on the "Test boolean as parent" "surveypro activity" page logged in as student1

    And I press "New response"
    And I press "Next page >>"
    Then I should see "Please choose a value"

    And I set the field "Is it true?" to "Yes"
    And I press "Next page >>"
    Then I should see "Write down your name"

    And I press "<< Previous page"
    Then the field "Is it true?" matches value "Yes"
    And I set the field "Is it true?" to "No"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "Is it true?" matches value "No"
    And I set the field "Is it true?" to "No answer"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "Is it true?" matches value "No answer"

    And I log out

    When I am on the "Test boolean as parent" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation
    And I follow "edit_item_2"
    And I expand all fieldsets
    And I set the field "Parent content" to "0"
    And I press "Save changes"

    And I log out

    # test the the child item correctly appear or not appear
    When I am on the "Test boolean as parent" "surveypro activity" page logged in as student1

    And I press "New response"
    And I press "Next page >>"
    Then I should see "Please choose a value"

    And I set the field "Is it true?" to "Yes"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "Is it true?" matches value "Yes"
    And I set the field "Is it true?" to "No"
    And I press "Next page >>"
    Then I should see "Write down your name"

    And I press "<< Previous page"
    Then the field "Is it true?" matches value "No"
    And I set the field "Is it true?" to "No answer"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "Is it true?" matches value "No answer"

    And I log out

    When I am on the "Test boolean as parent" "Activity editing" page logged in as teacher1
    And I expand all fieldsets
    And I set the field "Branches increase pages" to "0"
    And I press "Save and display"

    And I log out

    # test the the child item is correctly enabled or disabled
    When I am on the "Test boolean as parent" "surveypro activity" page logged in as student1

    And I press "New response"
    Then the "Write down your name" "field" should be disabled

    And I set the field "Is it true?" to "Yes"
    Then the "Write down your name" "field" should be disabled

    And I set the field "Is it true?" to "No"
    Then the "Write down your name" "field" should be enabled

    And I set the field "Is it true?" to "No answer"
    Then the "Write down your name" "field" should be disabled

    And I log out

    When I am on the "Test boolean as parent" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation
    And I follow "edit_item_2"
    And I expand all fieldsets
    And I set the field "Parent content" to "1"
    And I press "Save changes"

    And I log out

    # test the the child item is correctly enabled or disabled
    When I am on the "Test boolean as parent" "surveypro activity" page logged in as student1

    And I press "New response"
    Then the "Write down your name" "field" should be disabled

    And I set the field "Is it true?" to "Yes"
    Then the "Write down your name" "field" should be enabled

    And I set the field "Is it true?" to "No"
    Then the "Write down your name" "field" should be disabled

    And I set the field "Is it true?" to "No answer"
    Then the "Write down your name" "field" should be disabled
