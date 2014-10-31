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
      | id_surveypro_field_radiobutton_398003_0        | 1          |
      | id_surveypro_field_radiobutton_398004_1        | 1          |
      | id_surveypro_field_radiobutton_398005_2        | 1          |
      | id_surveypro_field_radiobutton_398006_3        | 1          |
      | id_surveypro_field_radiobutton_398009_4        | 1          |
      | id_surveypro_field_radiobutton_398010_0        | 1          |
      | id_surveypro_field_radiobutton_398011_1        | 1          |
      | id_surveypro_field_radiobutton_398012_2        | 1          |
      | id_surveypro_field_radiobutton_398015_3        | 1          |
      | id_surveypro_field_radiobutton_398016_4        | 1          |
      | id_surveypro_field_radiobutton_398017_0        | 1          |
      | id_surveypro_field_radiobutton_398018_1        | 1          |
      | id_surveypro_field_radiobutton_398021_2        | 1          |
      | id_surveypro_field_radiobutton_398022_3        | 1          |
      | id_surveypro_field_radiobutton_398023_4        | 1          |
      | id_surveypro_field_radiobutton_398024_0        | 1          |
      | id_surveypro_field_radiobutton_398027_1        | 1          |
      | id_surveypro_field_radiobutton_398028_2        | 1          |
      | id_surveypro_field_radiobutton_398029_3        | 1          |
      | id_surveypro_field_radiobutton_398030_4        | 1          |
      | id_surveypro_field_radiobutton_398033_0        | 1          |
      | id_surveypro_field_radiobutton_398034_1        | 1          |
      | id_surveypro_field_radiobutton_398035_2        | 1          |
      | id_surveypro_field_radiobutton_398036_3        | 1          |
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
      | id_surveypro_field_radiobutton_398003_0        | 1          |
      | id_surveypro_field_radiobutton_398004_1        | 1          |
      | id_surveypro_field_radiobutton_398005_2        | 1          |
      | id_surveypro_field_radiobutton_398006_3        | 1          |
      | id_surveypro_field_radiobutton_398009_4        | 1          |
      | id_surveypro_field_radiobutton_398010_0        | 1          |
      | id_surveypro_field_radiobutton_398011_1        | 1          |
      | id_surveypro_field_radiobutton_398012_2        | 1          |
      | id_surveypro_field_radiobutton_398015_3        | 1          |
      | id_surveypro_field_radiobutton_398016_4        | 1          |
      | id_surveypro_field_radiobutton_398017_0        | 1          |
      | id_surveypro_field_radiobutton_398018_1        | 1          |
      | id_surveypro_field_radiobutton_398021_2        | 1          |
      | id_surveypro_field_radiobutton_398022_3        | 1          |
      | id_surveypro_field_radiobutton_398023_4        | 1          |
      | id_surveypro_field_radiobutton_398024_0        | 1          |
      | id_surveypro_field_radiobutton_398027_1        | 1          |
      | id_surveypro_field_radiobutton_398028_2        | 1          |
      | id_surveypro_field_radiobutton_398029_3        | 1          |
      | id_surveypro_field_radiobutton_398030_4        | 1          |
      | id_surveypro_field_radiobutton_398033_0        | 1          |
      | id_surveypro_field_radiobutton_398034_1        | 1          |
      | id_surveypro_field_radiobutton_398035_2        | 1          |
      | id_surveypro_field_radiobutton_398036_3        | 1          |
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
      | id_surveypro_field_radiobutton_398003_0        | 1          |
      | id_surveypro_field_radiobutton_398004_1        | 1          |
      | id_surveypro_field_radiobutton_398005_2        | 1          |
      | id_surveypro_field_radiobutton_398006_3        | 1          |
      | id_surveypro_field_radiobutton_398007_4        | 1          |
      | id_surveypro_field_radiobutton_398008_0        | 1          |
      | id_surveypro_field_radiobutton_398009_1        | 1          |
      | id_surveypro_field_radiobutton_398010_2        | 1          |

      | id_surveypro_field_radiobutton_398013_3        | 1          |
      | id_surveypro_field_radiobutton_398014_4        | 1          |
      | id_surveypro_field_radiobutton_398015_0        | 1          |
      | id_surveypro_field_radiobutton_398016_1        | 1          |
      | id_surveypro_field_radiobutton_398017_2        | 1          |
      | id_surveypro_field_radiobutton_398018_3        | 1          |
      | id_surveypro_field_radiobutton_398019_4        | 1          |
      | id_surveypro_field_radiobutton_398020_0        | 1          |

      | id_surveypro_field_radiobutton_398023_1        | 1          |
      | id_surveypro_field_radiobutton_398024_2        | 1          |
      | id_surveypro_field_radiobutton_398025_3        | 1          |
      | id_surveypro_field_radiobutton_398026_4        | 1          |
      | id_surveypro_field_radiobutton_398027_0        | 1          |
      | id_surveypro_field_radiobutton_398028_1        | 1          |
      | id_surveypro_field_radiobutton_398029_2        | 1          |
      | id_surveypro_field_radiobutton_398030_3        | 1          |

      | id_surveypro_field_radiobutton_398033_4        | 1          |
      | id_surveypro_field_radiobutton_398034_0        | 1          |
      | id_surveypro_field_radiobutton_398035_1        | 1          |
      | id_surveypro_field_radiobutton_398036_2        | 1          |
      | id_surveypro_field_radiobutton_398037_3        | 1          |
      | id_surveypro_field_radiobutton_398038_4        | 1          |
      | id_surveypro_field_radiobutton_398039_0        | 1          |
      | id_surveypro_field_radiobutton_398040_1        | 1          |

      | id_surveypro_field_radiobutton_398043_2        | 1          |
      | id_surveypro_field_radiobutton_398044_3        | 1          |
      | id_surveypro_field_radiobutton_398045_4        | 1          |
      | id_surveypro_field_radiobutton_398046_0        | 1          |
      | id_surveypro_field_radiobutton_398047_1        | 1          |
      | id_surveypro_field_radiobutton_398048_2        | 1          |
      | id_surveypro_field_radiobutton_398049_3        | 1          |
      | id_surveypro_field_radiobutton_398050_4        | 1          |

      | id_surveypro_field_radiobutton_398053_0        | 1          |
      | id_surveypro_field_radiobutton_398054_1        | 1          |
      | id_surveypro_field_radiobutton_398055_2        | 1          |
      | id_surveypro_field_radiobutton_398056_3        | 1          |
      | id_surveypro_field_radiobutton_398057_4        | 1          |
      | id_surveypro_field_radiobutton_398058_0        | 1          |
      | id_surveypro_field_radiobutton_398059_1        | 1          |
      | id_surveypro_field_radiobutton_398060_2        | 1          |
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
