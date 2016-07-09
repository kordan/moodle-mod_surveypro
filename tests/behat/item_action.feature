@mod @mod_surveypro
Feature: Test item actions
  In order to validate each action issues through inline icons
  As a teacher
  I issue them and verify the outcome.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | numsections |
      | Course 1 | C1        | 0        | 3           |
    And I log in as "admin"

  @javascript
  Scenario: test item actions
    Given the following "activities" exist:
      | activity  | name           | intro              | course | idnumber   |
      | surveypro | Surveypro test | For testing backup | C1     | surveypro1 |
    And surveypro "Surveypro test" contains the following items:
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

    And I am on site homepage
    When I follow "Course 1"
    And I follow "Surveypro test"
    And I follow "Layout"

    Then I should see "22" visible items
    Then I should see "0" hidden items
    Then I should see "0" reserved items
    # 21 and not 22 because pagebreak doesn't have <a class"makereserved"...
    Then I should see "21" free items
    Then I should see "0" searchable items
    # Not 22 but...
    # -1 because fielupload can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because rate can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because pagebreak can't be searchable so doesn't have <a class"addtosearch"...
    Then I should see "19" not searchable items

    And I follow "addtosearch_item_3"
    And I follow "addtosearch_item_4"

    Then I should see "22" visible items
    Then I should see "0" hidden items
    Then I should see "0" reserved items
    # 21 and not 22 because pagebreak doesn't have <a class"makereserved"...
    Then I should see "21" free items
    Then I should see "2" searchable items
    # Not 20 but...
    # -1 because fielupload can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because rate can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because pagebreak can't be searchable so doesn't have <a class"addtosearch"...
    Then I should see "17" not searchable items

    And I follow "removesearch_item_3"

    Then I should see "22" visible items
    Then I should see "0" hidden items
    Then I should see "0" reserved items
    # 21 and not 22 because pagebreak doesn't have <a class"makereserved"...
    Then I should see "21" free items
    Then I should see "1" searchable items
    # Not 21 but...
    # -1 because fielupload can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because rate can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because pagebreak can't be searchable so doesn't have <a class"addtosearch"...
    Then I should see "18" not searchable items

    And I follow "makereserved_item_1"
    And I follow "makereserved_item_2"
    And I follow "makereserved_item_3"
    And I follow "makereserved_item_4"
    And I follow "makereserved_item_5"
    And I follow "makereserved_item_6"
    And I follow "makereserved_item_7"
    And I follow "makereserved_item_8"
    And I follow "makereserved_item_9"

    Then I should see "22" visible items
    Then I should see "0" hidden items
    Then I should see "9" reserved items
    # 12 and not 13 because pagebreak doesn't have <a class"makereserved"...
    Then I should see "12" free items
    Then I should see "1" searchable items
    # Not 21 but...
    # -1 because fielupload can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because rate can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because pagebreak can't be searchable so doesn't have <a class"addtosearch"...
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

    Then I should see "16" visible items
    Then I should see "0" hidden items
    Then I should see "6" reserved items
    # 9 and not 10 because pagebreak doesn't have <a class"makereserved"...
    Then I should see "9" free items
    Then I should see "1" searchable items
    # Not 15 but...
    # -1 because fielupload can't be searchable so doesn't have <a class"addtosearch"...
    # -1 because pagebreak can't be searchable so doesn't have <a class"addtosearch"...
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

    Then I should see "7" visible items
    Then I should see "9" hidden items
    # (reserved + free) must be == # of visibles
    Then I should see "3" reserved items
    Then I should see "4" free items
    # (searchable + not searchable) must be == # of visibles
    Then I should see "0" searchable items
    # Not 7 but...
    # -1 because fielupload can't be searchable so doesn't have <a class"addtosearch"...
    Then I should see "6" not searchable items
