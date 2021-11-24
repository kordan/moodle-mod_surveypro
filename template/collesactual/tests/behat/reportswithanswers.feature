@mod @mod_surveypro @surveyprotemplate @surveyprotemplate_collesactual
Feature: Test colles report for courses divided into groups and answers provided
  In order to test that reports are displayed even for courses divided into groups
  As student11 up to student32
  I fill a surveypro and ask for colles report

  @javascript
  Scenario: test colles actual reports with answers
    Given the following "courses" exist:
      | fullname                   | shortname      | category | groupmode |
      | Course divided into groups | Course grouped | 0        | 0         |
    And the following "groups" exist:
      | name    | course         | idnumber |
      | Group 1 | Course grouped | G1       |
      | Group 2 | Course grouped | G2       |
    And the following "users" exist:
      | username  | firstname | lastname | email                |
      | teacher1  | Teacher   | teacher  | teacher1@nowhere.net |
      | student11 | student11 | user11   | student1@nowhere.net |
      | student12 | student12 | user12   | student2@nowhere.net |
      | student21 | student21 | user21   | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user      | course         | role           |
      | teacher1  | Course grouped | editingteacher |
      | student11 | Course grouped | student        |
      | student12 | Course grouped | student        |
      | student21 | Course grouped | student        |
    And the following "permission overrides" exist:
      | capability                          | permission | role    | contextlevel | reference      |
      | mod/surveypro:editownsubmissions    | Allow      | student | Course       | Course grouped |
      | mod/surveypro:seeotherssubmissions  | Allow      | student | Course       | Course grouped |
      | mod/surveypro:editotherssubmissions | Allow      | student | Course       | Course grouped |
    And the following "group members" exist:
      | user     | group |
      | student11 | G1   |
      | student12 | G1   |
      | student21 | G2   |
    And the following "activities" exist:
      | activity  | name           | intro          | course         |
      | surveypro | Verify reports | Verify reports | Course grouped |

    And I log in as "teacher1"
    And I am on "Course divided into groups" course homepage
    And I follow "Verify reports"

    And I navigate to "Edit settings" in current page administration
    And I set the following fields to these values:
      | Group mode | Visible groups |
    And I press "Save and display"

    And I set the field "Master templates" to "COLLES (Actual)"
    And I press "Apply"

    And I log out

    # student11 logs in
    When I log in as "student11"
    And I am on "Course divided into groups" course homepage
    And I follow "Verify reports"

    And I press "New response"

    # student11 submits his response
    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_4_1             | 1           |
      | id_surveypro_field_radiobutton_5_0             | 1           |
      | id_surveypro_field_radiobutton_6_2             | 1           |
      | id_surveypro_field_radiobutton_7_4             | 1           |
      | id_surveypro_field_radiobutton_10_3            | 1           |
      | id_surveypro_field_radiobutton_11_0            | 1           |
      | id_surveypro_field_radiobutton_12_0            | 1           |
      | id_surveypro_field_radiobutton_13_1            | 1           |
      | id_surveypro_field_radiobutton_16_1            | 1           |
      | id_surveypro_field_radiobutton_17_2            | 1           |
      | id_surveypro_field_radiobutton_18_2            | 1           |
      | id_surveypro_field_radiobutton_19_3            | 1           |
      | id_surveypro_field_radiobutton_22_3            | 1           |
      | id_surveypro_field_radiobutton_23_4            | 1           |
      | id_surveypro_field_radiobutton_24_4            | 1           |
      | id_surveypro_field_radiobutton_25_4            | 1           |
      | id_surveypro_field_radiobutton_28_3            | 1           |
      | id_surveypro_field_radiobutton_29_2            | 1           |
      | id_surveypro_field_radiobutton_30_1            | 1           |
      | id_surveypro_field_radiobutton_31_0            | 1           |
      | id_surveypro_field_radiobutton_34_2            | 1           |
      | id_surveypro_field_radiobutton_35_4            | 1           |
      | id_surveypro_field_radiobutton_36_1            | 1           |
      | id_surveypro_field_radiobutton_37_3            | 1           |
      | How long did this survey take you to complete? | 2-3 min     |
      | Do you have any other comments?                | No, please. |
    And I press "Submit"

    And I log out

    # student12 logs in
    When I log in as "student12"
    And I am on "Course divided into groups" course homepage
    And I follow "Verify reports"
    And I press "New response"

    # student12 submits his response
    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_4_2             | 1           |
      | id_surveypro_field_radiobutton_5_1             | 1           |
      | id_surveypro_field_radiobutton_6_3             | 1           |
      | id_surveypro_field_radiobutton_7_4             | 1           |
      | id_surveypro_field_radiobutton_10_4            | 1           |
      | id_surveypro_field_radiobutton_11_0            | 1           |
      | id_surveypro_field_radiobutton_12_3            | 1           |
      | id_surveypro_field_radiobutton_13_2            | 1           |
      | id_surveypro_field_radiobutton_16_1            | 1           |
      | id_surveypro_field_radiobutton_17_4            | 1           |
      | id_surveypro_field_radiobutton_18_2            | 1           |
      | id_surveypro_field_radiobutton_19_0            | 1           |
      | id_surveypro_field_radiobutton_22_3            | 1           |
      | id_surveypro_field_radiobutton_23_1            | 1           |
      | id_surveypro_field_radiobutton_24_4            | 1           |
      | id_surveypro_field_radiobutton_25_2            | 1           |
      | id_surveypro_field_radiobutton_28_1            | 1           |
      | id_surveypro_field_radiobutton_29_4            | 1           |
      | id_surveypro_field_radiobutton_30_2            | 1           |
      | id_surveypro_field_radiobutton_31_1            | 1           |
      | id_surveypro_field_radiobutton_34_0            | 1           |
      | id_surveypro_field_radiobutton_35_4            | 1           |
      | id_surveypro_field_radiobutton_36_3            | 1           |
      | id_surveypro_field_radiobutton_37_2            | 1           |
      | How long did this survey take you to complete? | 2-3 min     |
      | Do you have any other comments?                | No, please. |
    And I press "Submit"

    And I log out

    # student21 logs in
    When I log in as "student21"
    And I am on "Course divided into groups" course homepage
    And I follow "Verify reports"

    And I press "New response"

    # student21 submits his response
    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_4_4             | 1           |
      | id_surveypro_field_radiobutton_5_2             | 1           |
      | id_surveypro_field_radiobutton_6_1             | 1           |
      | id_surveypro_field_radiobutton_7_0             | 1           |
      | id_surveypro_field_radiobutton_10_3            | 1           |
      | id_surveypro_field_radiobutton_11_4            | 1           |
      | id_surveypro_field_radiobutton_12_1            | 1           |
      | id_surveypro_field_radiobutton_13_0            | 1           |
      | id_surveypro_field_radiobutton_16_2            | 1           |
      | id_surveypro_field_radiobutton_17_2            | 1           |
      | id_surveypro_field_radiobutton_18_1            | 1           |
      | id_surveypro_field_radiobutton_19_4            | 1           |
      | id_surveypro_field_radiobutton_22_3            | 1           |
      | id_surveypro_field_radiobutton_23_0            | 1           |
      | id_surveypro_field_radiobutton_24_3            | 1           |
      | id_surveypro_field_radiobutton_25_2            | 1           |
      | id_surveypro_field_radiobutton_28_4            | 1           |
      | id_surveypro_field_radiobutton_29_1            | 1           |
      | id_surveypro_field_radiobutton_30_3            | 1           |
      | id_surveypro_field_radiobutton_31_0            | 1           |
      | id_surveypro_field_radiobutton_34_0            | 1           |
      | id_surveypro_field_radiobutton_35_1            | 1           |
      | id_surveypro_field_radiobutton_36_2            | 1           |
      | id_surveypro_field_radiobutton_37_4            | 1           |
      | How long did this survey take you to complete? | 2-3 min     |
      | Do you have any other comments?                | No, please. |
    And I press "Submit"

    And I log out

    And I log in as "teacher1"
    And I am on "Course divided into groups" course homepage
    And I follow "Verify reports"

    And I follow "Run Colles report > Summary report"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 1"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 2"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I follow "Survey" page in tab bar
    And I follow "Dashboard" page in tab bar
    And I follow "Run Colles report > Questions > Relevance report"
    # And I navigate to "Report > Colles report > Questions > Relevance" in current page administration
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 1"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 2"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I follow "Survey" page in tab bar
    And I follow "Dashboard" page in tab bar
    And I follow "Run Colles report > Questions > Reflective thinking report"
    # And I navigate to "Report > Colles report > Questions > Reflective thinking" in current page administration
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 1"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 2"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I follow "Survey" page in tab bar
    And I follow "Dashboard" page in tab bar
    And I follow "Run Colles report > Questions > Interactivity report"
    # And I navigate to "Report > Colles report > Questions > Interactivity" in current page administration
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 1"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 2"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I follow "Survey" page in tab bar
    And I follow "Dashboard" page in tab bar
    And I follow "Run Colles report > Questions > Tutor support report"
    # And I navigate to "Report > Colles report > Questions > Tutor support" in current page administration
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 1"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 2"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I follow "Survey" page in tab bar
    And I follow "Dashboard" page in tab bar
    And I follow "Run Colles report > Questions > Peer support report"
    # And I navigate to "Report > Colles report > Questions > Peer support" in current page administration
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 1"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 2"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I follow "Survey" page in tab bar
    And I follow "Dashboard" page in tab bar
    And I follow "Run Colles report > Questions > Interpretation report"
    # And I navigate to "Report > Colles report > Questions > Interpretation" in current page administration
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 1"
    Then I should not see "No responses were found in this survey for enrolled students."

    And I set the field "id_groupid" to "Group 2"
    Then I should not see "No responses were found in this survey for enrolled students."
