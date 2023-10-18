@mod @mod_surveypro
Feature: Execute item actions
  In order to validate each action issues through inline icons
  As teacher1
  I issue them and verify the outcome.

  Background:
    Given the following "courses" exist:
      | fullname          | shortname | category | numsections |
      | Test item actions | Test IA   | 0        | 3           |

  @javascript
  Scenario: Test simple item actions
    Given the following "activities" exist:
      | activity  | name                     | intro                | course  |
      | surveypro | Test simple item actions | To test item actions | Test IA |
    And surveypro "Test simple item actions" contains the following items:
      | type   | plugin      |
      | field  | age         |
      | field  | autofill    |
      | field  | boolean     |
      | field  | character   |
      | field  | checkbox    |
      | field  | date        |
      | field  | datetime    |
      | field  | fileupload  |
      | field  | integer     |
      | field  | multiselect |
      | field  | numeric     |
      | field  | radiobutton |
      | field  | rate        |
      | field  | recurrence  |
      | field  | select      |
      | field  | shortdate   |
      | field  | textarea    |
      | field  | time        |
      | format | pagebreak   |
      | format | label       |
      | format | fieldset    |
      | format | fieldsetend |
    And I am on the "Test simple item actions" "surveypro activity" page logged in as admin
    And I follow "Layout"

    Then I should see "0" reserved items
    # 21 and not 22 available items because pagebreak doesn't have <a class"makereserved"...
    Then I should see "21" available items
    Then I should see "22" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    # 19 and not 22 not searchable items because...
    # -1 because fielupload can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because rate can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because pagebreak can't be searchable so doesn't have <a class"addtosearch"...
    Then I should see "19" not searchable items
    Then I should see "0" searchable items

    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_3')]" "xpath_element"
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_4')]" "xpath_element"

    Then I should see "0" reserved items
    Then I should see "21" available items
    Then I should see "22" visible items
    Then I should see "0" hidden items
    Then I should see "2" searchable items
    Then I should see "17" not searchable items

    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'removefromsearch_item_3')]" "xpath_element"

    Then I should see "0" reserved items
    Then I should see "21" available items
    Then I should see "22" visible items
    Then I should see "0" hidden items
    Then I should see "1" searchable items
    Then I should see "18" not searchable items

    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'makereserved_item_1')]" "xpath_element"
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'makereserved_item_2')]" "xpath_element"
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'makereserved_item_3')]" "xpath_element"
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'makereserved_item_4')]" "xpath_element"
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'makereserved_item_5')]" "xpath_element"
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'makereserved_item_6')]" "xpath_element"
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'makereserved_item_7')]" "xpath_element"
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'makereserved_item_8')]" "xpath_element"
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'makereserved_item_9')]" "xpath_element"

    Then I should see "9" reserved items
    Then I should see "12" available items
    Then I should see "22" visible items
    Then I should see "0" hidden items
    Then I should see "1" searchable items
    Then I should see "18" not searchable items

    And I follow "delete_item_5"
    And I press "Yes"
    And I follow "delete_item_6"
    And I press "Yes"
    And I follow "delete_item_7"
    And I press "Yes"
    And I follow "delete_item_8"
    And I press "Yes"
    And I follow "delete_item_9"
    And I press "Yes"
    And I follow "delete_item_10"
    And I press "Yes"

    Then I should see "6" reserved items
    Then I should see "9" available items
    Then I should see "16" visible items
    Then I should see "0" hidden items
    Then I should see "1" searchable items
    Then I should see "13" not searchable items

    And I follow "hide_item_2"
    And I follow "hide_item_3"
    And I follow "hide_item_4"
    And I follow "hide_item_11"
    And I follow "hide_item_12"
    And I follow "hide_item_13"
    And I follow "hide_item_14"
    And I follow "hide_item_15"
    And I follow "hide_item_16"

    Then I should see "3" reserved items
    Then I should see "4" available items
    Then I should see "7" visible items
    Then I should see "9" hidden items
    Then I should see "0" searchable items
    Then I should see "6" not searchable items

  @javascript @_file_upload
  Scenario: Test complex item actions
    Given the following "activities" exist:
      | activity  | name                      | intro       | course  |
      | surveypro | Test complex item actions | To test CIA | Test IA |
    When I am on the "Test complex item actions" "mod_surveypro > User templates import" page logged in as "admin"
    And I upload "mod/surveypro/tests/fixtures/usertemplate/item_action_test.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "This course"

    And I press "Import"

    # now I am in the "Manage" page
    And I am on the "Test complex item actions" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) item_action_test.xml |
      | id_action_0    | 1                                  |
    And I press "Apply"

    And I follow "makereserved_item_2"
    Then I should see "Reserving the element"
    Then I should see "Very first parent"
    Then I should see "Dependencies are the elements in position: 3, 7, 4, 8, 5, 6, 9."
    And I press "Continue"

    Then I should see "8" reserved items
    Then I should see "6" available items
    Then I should see "14" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "14" not searchable items

    And I follow "makeavailable_item_7"
    Then I should see "Making available the element"
    Then I should see "Second generation, second parent question"
    Then I should see "Very first parent"
    Then I should see "So, in addition to the chosen element, you are going to make available the elements in position: 2, 3, 4, 8, 5, 6, 9."
    And I press "Continue"

    Then I should see "0" reserved items
    Then I should see "14" available items
    Then I should see "14" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "14" not searchable items

    And I follow "hide_item_2"
    Then I should see "Hiding the element"
    Then I should see "Very first parent"
    Then I should see "Dependencies are the elements in position: 3, 7, 4, 8, 5, 6, 9."
    And I press "Continue"

    Then I should see "0" reserved items
    Then I should see "6" available items
    Then I should see "6" visible items
    Then I should see "8" hidden items
    Then I should see "0" searchable items
    Then I should see "6" not searchable items

    And I follow "show_item_9"
    Then I should see "Showing the element"
    Then I should see "Fourth generation unique question"
    Then I should see "Ancestors are the elements in position: 8, 7, 2."
    And I press "Continue"

    Then I should see "0" reserved items
    Then I should see "10" available items
    Then I should see "10" visible items
    Then I should see "4" hidden items
    Then I should see "0" searchable items
    Then I should see "10" not searchable items

    And I follow "delete_item_9"
    Then I should see "Are you sure you want to delete the 'select' element:"
    Then I should see "Fourth generation unique question"
    And I press "No"

    Then I should see "0" reserved items
    Then I should see "10" available items
    Then I should see "10" visible items
    Then I should see "4" hidden items
    Then I should see "0" searchable items
    Then I should see "10" not searchable items

    And I follow "delete_item_1"
    Then I should see "Are you sure you want to delete the 'label' element:"
    Then I should see "First part of the test"
    And I press "Yes"

    Then I should see "0" reserved items
    Then I should see "9" available items
    Then I should see "9" visible items
    Then I should see "4" hidden items
    Then I should see "0" searchable items
    Then I should see "9" not searchable items

    And I follow "hide_item_1"
    Then I should see "Hiding the element"
    Then I should see "Very first parent"
    Then I should see "Dependencies are the elements in position: 6, 7, 8."
    And I press "No"

    Then I should see "0" reserved items
    Then I should see "9" available items
    Then I should see "9" visible items
    Then I should see "4" hidden items
    Then I should see "0" searchable items
    Then I should see "9" not searchable items

    And I follow "delete_item_1"
    Then I should see "Are you sure you want to delete the 'radio button' element:"
    Then I should see "Very first parent"
    Then I should see "The current element has child element(s) that are going to be deleted too."
    Then I should see "The child element(s) position is: 2, 6, 3, 7, 4, 5, 8."
    And I press "Continue"

    Then I should see "0" reserved items
    Then I should see "5" available items
    Then I should see "5" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "5" not searchable items

    And I follow "makereserved_item_5"
    Then I should see "Reserving the element"
    Then I should see "Second generation, third question"
    Then I should see "So, in addition to the chosen element, you are going to reserve the elements in position: 2, 3, 4."
    And I press "Continue"

    Then I should see "4" reserved items
    Then I should see "1" available items
    Then I should see "5" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "5" not searchable items

    And I follow "makeavailable_item_5"
    Then I should see "Making available the element"
    Then I should see "Second generation, third question"
    Then I should see "So, in addition to the chosen element, you are going to make available the elements in position: 2, 3, 4."
    And I press "Continue"

    Then I should see "0" reserved items
    Then I should see "5" available items
    Then I should see "5" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "5" not searchable items

    And I follow "makereserved_item_2"
    Then I should see "Reserving the element"
    Then I should see "Simple parent"
    Then I should see "Dependencies are the elements in position: 3, 4, 5."
    And I press "Continue"

    Then I should see "4" reserved items
    Then I should see "1" available items
    Then I should see "5" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "5" not searchable items

    And I follow "makeavailable_item_2"
    Then I should see "Making available the element"
    Then I should see "Simple parent"
    Then I should see "Dependencies are the elements in position: 3, 4, 5."
    And I press "Continue"

    Then I should see "0" reserved items
    Then I should see "5" available items
    Then I should see "5" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "5" not searchable items

    And I follow "makereserved_item_3"
    Then I should see "Reserving the element"
    Then I should see "Second generation, first question"
    Then I should see "So, in addition to the chosen element, you are going to reserve the elements in position: 2, 4, 5."
    And I press "Continue"

    Then I should see "4" reserved items
    Then I should see "1" available items
    Then I should see "5" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "5" not searchable items

    And I follow "makeavailable_item_3"
    Then I should see "Making available the element"
    Then I should see "Second generation, first question"
    Then I should see "So, in addition to the chosen element, you are going to make available the elements in position: 2, 4, 5."
    And I press "Continue"

    Then I should see "0" reserved items
    Then I should see "5" available items
    Then I should see "5" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "5" not searchable items

    And I follow "delete_item_5"
    Then I should see "Are you sure you want to delete the 'select' element:"
    Then I should see "Second generation, third question"
    And I press "Yes"

    Then I should see "0" reserved items
    Then I should see "4" available items
    Then I should see "4" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "4" not searchable items

    And I follow "delete_item_2"
    Then I should see "Are you sure you want to delete the 'radio button' element:"
    Then I should see "Simple parent"
    Then I should see "The child element(s) position is: 3, 4."
    And I press "Continue"

    Then I should see "0" reserved items
    Then I should see "1" available items
    Then I should see "1" visible items
    Then I should see "0" hidden items
    Then I should see "0" searchable items
    Then I should see "1" not searchable items

    And I follow "delete_item_1"
    Then I should see "Are you sure you want to delete the 'label' element:"
    Then I should see "Second part of the test"
    And I press "Yes"
