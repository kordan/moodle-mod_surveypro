@mod @mod_surveypro @surveyprofield @surveyprofield_textarea
Feature: Search using one and two textarea items
  In order to validate search form with one and two items
  As a student
  I search for submitted records.

  @javascript
  Scenario: Search using one and two textarea fields
    Given the following "courses" exist:
      | fullname                  | shortname   | category | numsections |
      | Test textarea search form | Search form | 0        | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Search form | editingteacher |
      | student1 | Search form | student        |
    And the following "activities" exist:
      | activity  | name                      | intro                  | course      |
      | surveypro | Textarea search form test | For searching purposes | Search form |
    And surveypro "Textarea search form test" has the following items:
      | type  | plugin   | settings                                                                                                                                                     |
      | field | textarea | {"content":"Describe your dog", "required":"1", "customnumber":"1", "arearows":"7", "areacols":"40", "minlength":"10", "maxlength":"25", "insearchform":"1"} |
      | field | textarea | {"content":"Describe your cat", "required":"1", "customnumber":"2", "arearows":"7", "areacols":"40", "minlength":"10", "maxlength":"25"}                     |
    And I am on the "Textarea search form test" "surveypro activity" page logged in as student1

    # Add the first record
    And I press "New response"
    And I set the following fields to these values:
      | Describe your dog | I love it, trust me |
      | Describe your cat | It's the pet I love |
    And I press "Submit"

    # Add the second record
    And I press "New response"
    And I set the following fields to these values:
      | Describe your dog | It's old but still nice |
      | Describe your cat | It's the pet I love     |
    And I press "Submit"

    # 1st search for submitted records
    Given I am on the "Textarea search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With only one field in the search form, "Ignore me" must not be in the form.
    And I should not see "*"

    And I set the field "Describe your dog" to "love"
    And I press "Search"
    Then I should see "1" submissions

    # Add the second search field
    And I am on the "Textarea search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_2')]" "xpath_element"

    # 2nd search for submitted records
    Given I am on the "Textarea search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    And I set the field "id_field_textarea_2_ignoreme" to "0"

    And I set the field "Describe your cat" to "love"
    And I press "Search"
    Then I should see "2" submissions

    # 3rd search for submitted records
    Given I am on the "Textarea search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the field "id_field_textarea_1_ignoreme" to "0"
    And I set the field "id_field_textarea_2_ignoreme" to "0"
    And I set the field "Describe your dog" to "love"
    And I set the field "Describe your cat" to "love"
    And I press "Search"
    Then I should see "1" submissions
