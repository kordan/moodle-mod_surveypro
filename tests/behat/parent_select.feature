@mod @mod_surveypro @surveyprofield
Feature: test the use of select as parent item
  In order to test select as parent item
  As a teacher
  I create a parent-child relation and I evaluate the outcome when relation is fulfilled and not fulfilled

  @javascript
  Scenario: test select as parent
    Given the following "courses" exist:
      | fullname         | shortname        | category | groupmode |
      | Select as parent | Select as parent | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course           | role           |
      | teacher1 | Select as parent | editingteacher |
      | student1 | Select as parent | student        |
    And the following "activities" exist:
      | activity  | name                  | intro                 | newpageforchild | course           | idnumber   |
      | surveypro | Test select as parent | Test select as parent | 1               | Select as parent | surveypro1 |
    And surveypro "Test select as parent" contains the following items:
      | type   | plugin |
      | field  | select |
    And I log in as "teacher1"
    And I am on "Select as parent" course homepage
    And I follow "Test select as parent"
    And I follow "Layout"

    # add a short text item
    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Write down your name                                         |
      | Parent element | Select [1]: Where do you usually spend your summer holidays? |
      | Parent content | mountain                                                     |
    And I press "Add"

    And I log out

    # test the the child item correctly appear or not appear
    When I log in as "student1"
    And I am on "Select as parent" course homepage
    And I follow "Test select as parent"

    And I press "New response"
    And I set the field "Where do you usually spend your summer holidays?" to "sea"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "Where do you usually spend your summer holidays?" matches value "sea"
    And I set the field "Where do you usually spend your summer holidays?" to "mountain"
    And I press "Next page >>"
    Then I should see "Write down your name"

    And I press "<< Previous page"
    Then the field "Where do you usually spend your summer holidays?" matches value "mountain"
    And I set the field "Where do you usually spend your summer holidays?" to "No answer"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "Where do you usually spend your summer holidays?" matches value "No answer"
    And I set the field "Where do you usually spend your summer holidays?" to "Choose..."
    And I press "Next page >>"
    Then I should see "Please choose an option"

    And I log out

    And I log in as "teacher1"
    And I am on "Select as parent" course homepage
    And I follow "Test select as parent"
    And I follow "Layout"
    And I follow "edit_item_2"
    And I expand all fieldsets
    And I set the field "Parent content" to "sea"
    And I press "Save changes"

    And I log out

    # test the the child item correctly appear or not appear
    When I log in as "student1"
    And I am on "Select as parent" course homepage
    And I follow "Test select as parent"

    And I press "New response"
    And I set the field "Where do you usually spend your summer holidays?" to "sea"
    And I press "Next page >>"
    Then I should see "Write down your name"

    And I press "<< Previous page"
    Then the field "Where do you usually spend your summer holidays?" matches value "sea"
    And I set the field "Where do you usually spend your summer holidays?" to "mountain"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "Where do you usually spend your summer holidays?" matches value "mountain"
    And I set the field "Where do you usually spend your summer holidays?" to "No answer"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "Where do you usually spend your summer holidays?" matches value "No answer"
    And I set the field "Where do you usually spend your summer holidays?" to "Choose..."
    And I press "Next page >>"
    Then I should see "Please choose an option"

    And I log out

    And I log in as "teacher1"
    And I am on "Select as parent" course homepage
    And I follow "Test select as parent"
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "Branches increase pages" to "0"
    And I press "Save and display"

    And I log out

    # test the the child item is correctly enabled or disabled
    When I log in as "student1"
    And I am on "Select as parent" course homepage
    And I follow "Test select as parent"

    And I press "New response"
    And I set the field "Where do you usually spend your summer holidays?" to "sea"
    Then the "Write down your name" "field" should be enabled

    And I set the field "Where do you usually spend your summer holidays?" to "mountain"
    Then the "Write down your name" "field" should be disabled

    And I set the field "Where do you usually spend your summer holidays?" to "No answer"
    Then the "Write down your name" "field" should be disabled

    And I set the field "Where do you usually spend your summer holidays?" to "Choose..."
    Then the "Write down your name" "field" should be disabled

    And I log out

    And I log in as "teacher1"
    And I am on "Select as parent" course homepage
    And I follow "Test select as parent"
    And I follow "Layout"
    And I follow "edit_item_2"
    And I expand all fieldsets
    And I set the field "Parent content" to "mountain"
    And I press "Save changes"

    And I log out

    # test the the child item is correctly enabled or disabled
    When I log in as "student1"
    And I am on "Select as parent" course homepage
    And I follow "Test select as parent"

    And I press "New response"
    And I set the field "Where do you usually spend your summer holidays?" to "sea"
    Then the "Write down your name" "field" should be disabled

    And I set the field "Where do you usually spend your summer holidays?" to "mountain"
    Then the "Write down your name" "field" should be enabled

    And I set the field "Where do you usually spend your summer holidays?" to "No answer"
    Then the "Write down your name" "field" should be disabled

    And I set the field "Where do you usually spend your summer holidays?" to "Choose..."
    Then the "Write down your name" "field" should be disabled
