@mod @mod_surveypro
Feature: apply a COLLES mastertemplate to test graphs
  In order to verify graphs for COLLES mastertemplates // Why this feature is useful
  As a teacher                                         // It can be 'an admin', 'a teacher', 'a student', 'a guest', 'a user', 'a tests writer' and 'a developer'
  I need to apply a mastertemplate                     // The feature we want

  Background:
    Given the following "courses" exist:
      | fullname              | shortname   | category | groupmode |
      | To test COLLES graphs | Test graphs | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
      | student1 | Student   | 1        | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Test graphs | editingteacher |
      | student1 | Test graphs | student        |
    And I log in as "teacher1"
    And I follow "To test COLLES graphs"
    And I turn editing mode on

  @javascript
  Scenario: apply COLLES (Actual) master template, add a record and call reports
    When I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | Run COLLES report                           |
      | Description | This is a surveypro test to test COLLES graphs |
    And I follow "Run COLLES report"
    And I set the field "Master templates" to "COLLES (Actual)"
    And I press "Create"
    Then I should see "In this online unit, I found that..."
    Then I should see "my learning focuses on issues that interest me."

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "To test COLLES graphs"
    And I follow "Run COLLES report"
    And I press "New response"

    # student1 submits his first response
    And I expand all fieldsets
    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_4_0             | 1          |
      | id_surveypro_field_radiobutton_5_1             | 1          |
      | id_surveypro_field_radiobutton_6_2             | 1          |
      | id_surveypro_field_radiobutton_7_3             | 1          |
      | id_surveypro_field_radiobutton_10_4            | 1          |
      | id_surveypro_field_radiobutton_11_0            | 1          |
      | id_surveypro_field_radiobutton_12_1            | 1          |
      | id_surveypro_field_radiobutton_13_2            | 1          |
      | id_surveypro_field_radiobutton_16_3            | 1          |
      | id_surveypro_field_radiobutton_17_4            | 1          |
      | id_surveypro_field_radiobutton_18_0            | 1          |
      | id_surveypro_field_radiobutton_19_1            | 1          |
      | id_surveypro_field_radiobutton_22_2            | 1          |
      | id_surveypro_field_radiobutton_23_3            | 1          |
      | id_surveypro_field_radiobutton_24_4            | 1          |
      | id_surveypro_field_radiobutton_25_0            | 1          |
      | id_surveypro_field_radiobutton_28_1            | 1          |
      | id_surveypro_field_radiobutton_29_2            | 1          |
      | id_surveypro_field_radiobutton_30_3            | 1          |
      | id_surveypro_field_radiobutton_31_4            | 1          |
      | id_surveypro_field_radiobutton_34_0            | 1          |
      | id_surveypro_field_radiobutton_35_1            | 1          |
      | id_surveypro_field_radiobutton_36_2            | 1          |
      | id_surveypro_field_radiobutton_37_3            | 1          |
      | How long did this survey take you to complete? | 2-3 min    |
      | Do you have any other comments?                | Am I sexy? |
    And I press "Submit"

    And I navigate to "Colles report" node in "Surveypro administration > Report"
    Then I should not see "Summary report"

    And I log out

    When I log in as "teacher1"
    And I follow "To test COLLES graphs"
    And I follow "Run COLLES report"

    And I navigate to "summary" node in "Surveypro administration > Report > Colles report"
    Then I should not see "Summary report"

    And I navigate to "scales" node in "Surveypro administration > Report > Colles report"
    Then I should not see "Scales report"

    And I navigate to "questions" node in "Surveypro administration > Report > Colles report"
    Then I should not see "Questions report"

  @javascript
  Scenario: apply COLLES (Preferred) master template, add a record and call reports
    When I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | Run COLLES report                           |
      | Description | This is a surveypro test to test COLLES graphs |
    And I follow "Run COLLES report"
    And I set the field "Master templates" to "COLLES (Preferred)"
    And I press "Create"
    Then I should see "In this online unit, I prefer that..."
    Then I should see "my learning focuses on issues that interest me."

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "To test COLLES graphs"
    And I follow "Run COLLES report"
    And I press "New response"

    # student1 submits his first response
    And I expand all fieldsets
    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_4_0             | 1          |
      | id_surveypro_field_radiobutton_5_1             | 1          |
      | id_surveypro_field_radiobutton_6_2             | 1          |
      | id_surveypro_field_radiobutton_7_3             | 1          |
      | id_surveypro_field_radiobutton_10_4            | 1          |
      | id_surveypro_field_radiobutton_11_0            | 1          |
      | id_surveypro_field_radiobutton_12_1            | 1          |
      | id_surveypro_field_radiobutton_13_2            | 1          |
      | id_surveypro_field_radiobutton_16_3            | 1          |
      | id_surveypro_field_radiobutton_17_4            | 1          |
      | id_surveypro_field_radiobutton_18_0            | 1          |
      | id_surveypro_field_radiobutton_19_1            | 1          |
      | id_surveypro_field_radiobutton_22_2            | 1          |
      | id_surveypro_field_radiobutton_23_3            | 1          |
      | id_surveypro_field_radiobutton_24_4            | 1          |
      | id_surveypro_field_radiobutton_25_0            | 1          |
      | id_surveypro_field_radiobutton_28_1            | 1          |
      | id_surveypro_field_radiobutton_29_2            | 1          |
      | id_surveypro_field_radiobutton_30_3            | 1          |
      | id_surveypro_field_radiobutton_31_4            | 1          |
      | id_surveypro_field_radiobutton_34_0            | 1          |
      | id_surveypro_field_radiobutton_35_1            | 1          |
      | id_surveypro_field_radiobutton_36_2            | 1          |
      | id_surveypro_field_radiobutton_37_3            | 1          |
      | How long did this survey take you to complete? | 2-3 min    |
      | Do you have any other comments?                | Am I sexy? |
    And I press "Submit"

    And I navigate to "Colles report" node in "Surveypro administration > Report"
    Then I should not see "Summary report"

    And I log out

    When I log in as "teacher1"
    And I follow "To test COLLES graphs"
    And I follow "Run COLLES report"

    And I navigate to "summary" node in "Surveypro administration > Report > Colles report"
    Then I should not see "Summary report"

    And I navigate to "scales" node in "Surveypro administration > Report > Colles report"
    Then I should not see "Scales report"

    And I navigate to "questions" node in "Surveypro administration > Report > Colles report"
    Then I should not see "Questions report"

  @javascript
  Scenario: apply COLLES (Actual Preferred) master template, add a record and call reports
    When I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | Run COLLES report                           |
      | Description | This is a surveypro test to test COLLES graphs |
    And I follow "Run COLLES report"
    And I set the field "Master templates" to "COLLES (Actual Preferred)"
    And I press "Create"
    Then I should see "I prefer that my learning focuses on issues that interest me."
    Then I should see "I found that my learning focuses on issues that interest me."

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "To test COLLES graphs"
    And I follow "Run COLLES report"
    And I press "New response"

    # student1 submits his first response
    And I expand all fieldsets
    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_4_0             | 1          |
      | id_surveypro_field_radiobutton_5_1             | 1          |
      | id_surveypro_field_radiobutton_6_2             | 1          |
      | id_surveypro_field_radiobutton_7_3             | 1          |
      | id_surveypro_field_radiobutton_8_4             | 1          |
      | id_surveypro_field_radiobutton_9_0             | 1          |
      | id_surveypro_field_radiobutton_10_1            | 1          |
      | id_surveypro_field_radiobutton_11_2            | 1          |

      | id_surveypro_field_radiobutton_14_3            | 1          |
      | id_surveypro_field_radiobutton_15_4            | 1          |
      | id_surveypro_field_radiobutton_16_0            | 1          |
      | id_surveypro_field_radiobutton_17_1            | 1          |
      | id_surveypro_field_radiobutton_18_2            | 1          |
      | id_surveypro_field_radiobutton_19_3            | 1          |
      | id_surveypro_field_radiobutton_20_4            | 1          |
      | id_surveypro_field_radiobutton_21_0            | 1          |

      | id_surveypro_field_radiobutton_24_1            | 1          |
      | id_surveypro_field_radiobutton_25_2            | 1          |
      | id_surveypro_field_radiobutton_26_3            | 1          |
      | id_surveypro_field_radiobutton_27_4            | 1          |
      | id_surveypro_field_radiobutton_28_0            | 1          |
      | id_surveypro_field_radiobutton_29_1            | 1          |
      | id_surveypro_field_radiobutton_30_2            | 1          |
      | id_surveypro_field_radiobutton_31_3            | 1          |

      | id_surveypro_field_radiobutton_34_4            | 1          |
      | id_surveypro_field_radiobutton_35_0            | 1          |
      | id_surveypro_field_radiobutton_36_1            | 1          |
      | id_surveypro_field_radiobutton_37_2            | 1          |
      | id_surveypro_field_radiobutton_38_3            | 1          |
      | id_surveypro_field_radiobutton_39_4            | 1          |
      | id_surveypro_field_radiobutton_40_0            | 1          |
      | id_surveypro_field_radiobutton_41_1            | 1          |

      | id_surveypro_field_radiobutton_44_2            | 1          |
      | id_surveypro_field_radiobutton_45_3            | 1          |
      | id_surveypro_field_radiobutton_46_4            | 1          |
      | id_surveypro_field_radiobutton_47_0            | 1          |
      | id_surveypro_field_radiobutton_48_1            | 1          |
      | id_surveypro_field_radiobutton_49_2            | 1          |
      | id_surveypro_field_radiobutton_50_3            | 1          |
      | id_surveypro_field_radiobutton_51_4            | 1          |

      | id_surveypro_field_radiobutton_54_0            | 1          |
      | id_surveypro_field_radiobutton_55_1            | 1          |
      | id_surveypro_field_radiobutton_56_2            | 1          |
      | id_surveypro_field_radiobutton_57_3            | 1          |
      | id_surveypro_field_radiobutton_58_4            | 1          |
      | id_surveypro_field_radiobutton_59_0            | 1          |
      | id_surveypro_field_radiobutton_60_1            | 1          |
      | id_surveypro_field_radiobutton_61_2            | 1          |

      | How long did this survey take you to complete? | 2-3 min    |
      | Do you have any other comments?                | Am I sexy? |
    And I press "Submit"

    And I navigate to "Colles report" node in "Surveypro administration > Report"
    Then I should not see "Summary report"

    And I log out

    When I log in as "teacher1"
    And I follow "To test COLLES graphs"
    And I follow "Run COLLES report"

    And I navigate to "summary" node in "Surveypro administration > Report > Colles report"
    Then I should not see "Summary report"

    And I navigate to "scales" node in "Surveypro administration > Report > Colles report"
    Then I should not see "Scales report"

    And I navigate to "questions" node in "Surveypro administration > Report > Colles report"
    Then I should not see "Questions report"
