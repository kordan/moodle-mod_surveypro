@mod @mod_surveypro @surveyprofield @surveyprofield_rate
Feature: test the use of rate setup form
  In order to test rate setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: test rate setup form
    Given the following "courses" exist:
      | fullname        | shortname       | category | groupmode |
      | Rate setup form | Rate setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Rate setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                 | intro                | course          | idnumber   |
      | surveypro | Test rate setup form | Test rate setup form | Rate setup form | surveypro1 |
    And surveypro "Test rate setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I log in as "teacher1"
    And I follow "Rate setup form"
    And I follow "Test rate setup form"
    And I follow "Layout"

    # add an rate item
    And I set the field "typeplugin" to "Rate"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | How confident are you with the following languages? |
      | Required                 | 1                                                   |
      | Indent                   | 1                                                   |
      | Element number           | II.a                                                |
      | Hide filling instruction | 1                                                   |
      | Variable                 | R1                                                  |
      | Additional note          | Additional note                                     |
      | Hidden                   | 1                                                   |
      | Reserved                 | 1                                                   |
      | Parent element           | Boolean [1]: Is this true?                          |
      | Parent content           | 1                                                   |
      | Element style            | dropdown menu                                       |
    And I set the field "Options" to multiline:
      """

      Italian


        Spanish
English
      French


      German

      Deutch

      """
    And I set the field "Rates" to multiline:
      """
         Mother tongue
      Very confident

Not enought




      Completely unknown

      """
    And I set the following fields to these values:
      | id_defaultoption_1       | 1                                                   |
    And I set the field "id_defaultvalue" to multiline:
      """
      Not enought


          Not enought



      Not enought
      Not enought


      """
    And I set the following fields to these values:
      | Download format          | list of options with corresponding values of rates  |
      | Force different rates    | 1                                                   |
    And I press "Add"

    Then I should see "Number of rates is not enough to force different rates"
    Then I should see "Defaults have to be different when different rates is required"
    And I set the field "Rates" to multiline:
      """


          Mother tongue
      Very confident


      100 words vocabulary

      Not enought
      Really ridicolous

            Completely unknown


      """
    And I set the field "id_defaultvalue" to multiline:
      """


      Completely unknown

             Absolutely ridicolous
      Not enought


      100 words vocabulary
      """
    And I press "Add"

    Then I should see "The default item \"Absolutely ridicolous\" was not found among rates"
    And I set the field "id_defaultvalue" to multiline:
      """

         Completely unknown
      Really ridicolous


      Not enought


100 words vocabulary
      """
    And I press "Add"

    Then I should see "Number of defaults has to be equal to the number of options"
    And I set the field "id_defaultvalue" to multiline:
      """

         Completely unknown
      Really ridicolous


      Not enought


100 words vocabulary
      Very confident
      Mother tongue




      """
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "How confident are you with the following languages?"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Element number" matches value "II.a"
    Then the field "Hide filling instruction" matches value "1"
    Then the field "Variable" matches value "R1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is this true?"
    Then the field "Parent content" matches value "1"
    Then the field "Element style" matches value "dropdown menu"
    Then the field "Options" matches multiline:
      """
      Italian
      Spanish
      English
      French
      German
      Deutch
      """
    Then the field "Rates" matches multiline:
      """
      Mother tongue
      Very confident
      100 words vocabulary
      Not enought
      Really ridicolous
      Completely unknown
      """
    Then the field "id_defaultoption_1" matches value "1"
    Then the field "defaultvalue" matches multiline:
      """
      Completely unknown
      Really ridicolous
      Not enought
      100 words vocabulary
      Very confident
      Mother tongue
      """
    Then the field "Download format" matches value "list of options with corresponding values of rates"
    Then the field "Force different rates" matches value "1"
    And I press "Cancel"

    And I follow "show_item_2"
    And I follow "Preview"
    Then I should see "II.a:"
    Then I should see "How confident are you with the following languages?"
    Then the field "id_surveypro_field_rate_2_0" matches value "Completely unknown"
    Then the field "id_surveypro_field_rate_2_1" matches value "Really ridicolous"
    Then the field "id_surveypro_field_rate_2_2" matches value "Not enought"
    Then the field "id_surveypro_field_rate_2_3" matches value "100 words vocabulary"
    Then the field "id_surveypro_field_rate_2_4" matches value "Very confident"
    Then the field "id_surveypro_field_rate_2_5" matches value "Mother tongue"
    Then I should see "Additional note"

    And I follow "layout_elements"
    And I follow "edit_item_2"
    And I set the following fields to these values:
      | Content                  | How confident are you with the following languages? |
      | Required                 | 1                                                   |
      | Element number           | II.b                                                |
      | Hide filling instruction | 0                                                   |
      | Variable                 | R2                                                  |
      | Additional note          | One more additional note                            |
      | Parent element           | Boolean [1]: Is this true?                          |
      | Parent content           | 0                                                   |
      | Element style            | radio buttons                                       |
    And I press "Save as new"

    And I follow "Preview"
    Then I should see "II.b:"
    Then the field "id_surveypro_field_rate_3_0_0" matches value "0"
    Then the field "id_surveypro_field_rate_3_0_1" matches value "0"
    Then the field "id_surveypro_field_rate_3_0_2" matches value "0"
    Then the field "id_surveypro_field_rate_3_0_3" matches value "0"
    Then the field "id_surveypro_field_rate_3_0_4" matches value "0"
    Then the field "id_surveypro_field_rate_3_0_5" matches value "1"

    Then the field "id_surveypro_field_rate_3_1_0" matches value "0"
    Then the field "id_surveypro_field_rate_3_1_1" matches value "0"
    Then the field "id_surveypro_field_rate_3_1_2" matches value "0"
    Then the field "id_surveypro_field_rate_3_1_3" matches value "0"
    Then the field "id_surveypro_field_rate_3_1_4" matches value "1"
    Then the field "id_surveypro_field_rate_3_1_5" matches value "0"

    Then the field "id_surveypro_field_rate_3_2_0" matches value "0"
    Then the field "id_surveypro_field_rate_3_2_1" matches value "0"
    Then the field "id_surveypro_field_rate_3_2_2" matches value "0"
    Then the field "id_surveypro_field_rate_3_2_3" matches value "1"
    Then the field "id_surveypro_field_rate_3_2_4" matches value "0"
    Then the field "id_surveypro_field_rate_3_2_5" matches value "0"

    Then the field "id_surveypro_field_rate_3_3_0" matches value "0"
    Then the field "id_surveypro_field_rate_3_3_1" matches value "0"
    Then the field "id_surveypro_field_rate_3_3_2" matches value "1"
    Then the field "id_surveypro_field_rate_3_3_3" matches value "0"
    Then the field "id_surveypro_field_rate_3_3_4" matches value "0"
    Then the field "id_surveypro_field_rate_3_3_5" matches value "0"

    Then the field "id_surveypro_field_rate_3_4_0" matches value "0"
    Then the field "id_surveypro_field_rate_3_4_1" matches value "1"
    Then the field "id_surveypro_field_rate_3_4_2" matches value "0"
    Then the field "id_surveypro_field_rate_3_4_3" matches value "0"
    Then the field "id_surveypro_field_rate_3_4_4" matches value "0"
    Then the field "id_surveypro_field_rate_3_4_5" matches value "0"

    Then the field "id_surveypro_field_rate_3_5_0" matches value "1"
    Then the field "id_surveypro_field_rate_3_5_1" matches value "0"
    Then the field "id_surveypro_field_rate_3_5_2" matches value "0"
    Then the field "id_surveypro_field_rate_3_5_3" matches value "0"
    Then the field "id_surveypro_field_rate_3_5_4" matches value "0"
    Then the field "id_surveypro_field_rate_3_5_5" matches value "0"
    Then I should see "One more additional note"
