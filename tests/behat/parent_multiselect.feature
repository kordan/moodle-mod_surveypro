@mod @mod_surveypro @surveyprofield
Feature: test the use of multiselect as parent item
  In order to test multiselect as parent item
  As a teacher
  I create a parent-child relation and I evaluate the outcome when relation is fulfilled and not fulfilled

  @javascript
  Scenario: test multiselect as parent
    Given the following "courses" exist:
      | fullname              | shortname             | category | groupmode |
      | Multiselect as parent | Multiselect as parent | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                | role           |
      | teacher1 | Multiselect as parent | editingteacher |
      | student1 | Multiselect as parent | student        |
    And the following "activities" exist:
      | activity  | name                       | intro                      | newpageforchild | course                | idnumber   |
      | surveypro | Test multiselect as parent | Test multiselect as parent | 1               | Multiselect as parent | surveypro1 |
    And surveypro "Test multiselect as parent" contains the following items:
      | type   | plugin      |
      | field  | multiselect |
    And I log in as "teacher1"
    And I am on "Multiselect as parent" course homepage
    And I follow "Test multiselect as parent"
    And I follow "Layout"

    # add a short text item
    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Write down your name                                         |
      | Parent element | Multiple selection [1]: What do you usually get for break... |
      | Parent content | milk                                                         |
    And I press "Add"

    And I log out

    # test the the child item correctly appear or not appear
    When I log in as "student1"
    And I am on "Multiselect as parent" course homepage
    And I follow "Test multiselect as parent"

    And I press "New response"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value ""
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk"
    And I press "Next page >>"
    Then I should see "Write down your name"

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "1"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value ""
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "1"
    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "0"
    And I set the field "id_surveypro_field_multiselect_1" to "sugar"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "sugar"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "chocolate"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "chocolate"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk, sugar"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, jam"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk, jam"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, chocolate"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk, chocolate"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar, jam"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk, sugar, jam"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar, jam, chocolate"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk, sugar, jam, chocolate"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "1"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value ""
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "1"

    And I log out

    And I log in as "teacher1"
    And I am on "Multiselect as parent" course homepage
    And I follow "Test multiselect as parent"
    And I follow "Layout"
    And I follow "edit_item_2"
    And I expand all fieldsets
    And I set the multiline field "Parent content" to "milk\nchocolate"
    And I press "Save changes"

    And I log out

    # test the the child item correctly appear or not appear
    When I log in as "student1"
    And I am on "Multiselect as parent" course homepage
    And I follow "Test multiselect as parent"

    And I press "New response"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value ""
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "sugar"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "sugar"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk, sugar"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, jam"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk, jam"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, chocolate"
    And I press "Next page >>"
    Then I should see "Write down your name"

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk, chocolate"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "1"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value ""
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "1"
    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar, jam"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk, sugar, jam"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar, jam, chocolate"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value "milk, sugar, jam, chocolate"
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "0"
    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "1"
    And I press "Next page >>"
    Then I should see "On the basis of the answers provided, no more elements remain to display."

    And I press "<< Previous page"
    Then the field "id_surveypro_field_multiselect_1" matches value ""
    Then the field "id_surveypro_field_multiselect_1_noanswer" matches value "1"

    And I log out

    And I log in as "teacher1"
    And I am on "Multiselect as parent" course homepage
    And I follow "Test multiselect as parent"
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "Branches increase pages" to "0"
    And I press "Save and display"

    And I log out

    # test the the child item is correctly enabled or disabled
    When I log in as "student1"
    And I am on "Multiselect as parent" course homepage
    And I follow "Test multiselect as parent"

    And I press "New response"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "milk"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "sugar"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "jam"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "milk, jam"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "milk, chocolate"
    Then the "Write down your name" "field" should be enabled

    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "1"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "0"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar, jam"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar, jam, chocolate"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "1"
    Then the "Write down your name" "field" should be disabled

    And I log out

    And I log in as "teacher1"
    And I am on "Multiselect as parent" course homepage
    And I follow "Test multiselect as parent"
    And I follow "Layout"
    And I follow "edit_item_2"
    And I expand all fieldsets
    And I set the field "Parent content" to "milk"
    And I press "Save changes"

    And I log out

    # test the the child item is correctly enabled or disabled
    When I log in as "student1"
    And I am on "Multiselect as parent" course homepage
    And I follow "Test multiselect as parent"

    And I press "New response"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "milk"
    Then the "Write down your name" "field" should be enabled

    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "1"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "0"
    And I set the field "id_surveypro_field_multiselect_1" to "sugar"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "jam"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "milk, jam"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar, jam"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1" to "milk, sugar, jam, chocolate"
    Then the "Write down your name" "field" should be disabled

    And I set the field "id_surveypro_field_multiselect_1_noanswer" to "1"
    Then the "Write down your name" "field" should be disabled
