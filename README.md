moodle-mod_surveypro
====================
This module comes from the need to perform a very closely validated data collection to gather customized data. It allows the creation of custom survey assembling fields and format elements.
Fields and formats, usually called "elements", are two of the four managed plugin. Surveypro plugins include: fields, formats, templates and report.

Surveypro ships with a set of 21 built-in elements as plugins. They include 17 fields and 4 formats. Fields are the user items needed by the student to enter their responses while formats are "read only" elements like page break, fieldset or labels. If you can't find the field or the format you need, you can always ask a developer to build the plugin closely matching your needs. Fields share a set of properties. In addition to these, each field manages its own specific features. Among common properties, usually, can be found: mandatory, indent, position of the element content, custom number, variable name, note, availability, validation, default and branching.
The meaning of the less common properties is:
- Note is free text that the editing teacher can use to provide a custom explanation of an element.
- Availability defines who will see the field in the form. It can be:
-- all: each user will see the field in the survey form;
-- hidden: not any user will see the field in the survey form;
-- advanced: only students with specific permissions (accessadvanceditems) will see the field in the survey form;
-- search: the field will be available in the search form too (if not hidden).
- Validation options define the range of allowed responses for the students.
- Default is what the blank form will display as predefined value of an element.

Templates are divided into two subcategories: user-templates and master-templates. Master template is the plugin to quickly create a classic survey such as ATTLS, COLLES's or Critical Incidents.
Editing teacher is allowed to create his own mater templates based on a manually created survey. The added value of master templates is the multilanguage behaviour. Once a course creator saves a survey as master template, in order to use it in a different moodle instance he needs to copy it to the filesystem of the target moodle instance and to navigate the notification page. Each language added to the lang folder of the master template package will be available to students in the target moodle instance. This means that the same survey will be served to students in their own language if available.
User template is the plugin for editing teachers to quickly make snapshots of surveys to share and apply them a second time without the need to copy them to the filesystem. Case uses for user templates are, for instance,
1) An editing teacher is frequently called to create custom surveys where a fixed set of elements is included. Let's say: First name, Last name, Date of birth, Gender. To save time, the editing teacher can create a surveypro including this set of elements and then save the corresponding user-template. Later, whenever he/she is called to build a new surveypro with the set of elements of the first surveypro, he/she will apply the generated user-template to the new surveypro. All fields of the user-template will be included at once.
2) An editing teacher is asked to create the same surveypro in different courses. He can store a usertemplate at some level (category level, user level, or site level as last chance) and he/she will find it in each new course (of the same shared level) ready to be applied.
3) Two editing teachers are working on their local moodle instance drafting the same surveypro. Saving and sharing by email the corresponding usertemplate is a quick way to share progress achieved.

Report is the plugin to make report of the gathered data. Reports are available from the administration menu.
COLLES's report manages the same graphs as the current Moodle core survey module. Reports are pluggable so if you can't find the report matching your needs, you can always ask a developer to develop the one you really need and add it to your surveypro copy.

Branching
Each element can be defined as "child" of a different element upon a condition. Each element can have more than one child, but can only have one parent through a simple "parent = answer" condition. This means that it is possible to create a three elements surveypro, for instance, where the availability of the last two items depends from the answer provided by the student to the first element. The first element, in this example, has two children while each child has only one parent.

Relational databases
Surveypro can be used to create a relational database. Surveypro can not strictly create a relational database but gives a way to help in this purpose. Once an editing teacher creates a survey he/she can include an autofill element. This can be configured to auto-answer information about the user or the moodle course holding the surveypro instance or the surveypro instance itself. Let's imagine an editing teacher creates two surveypro instances. One asking for information about the living country of the student and one about habits of each of his/her family member. Once each surveypro is equipped with an autofill element providing the student userid it will be simple, once downloaded each gathered record, to make the 1 to n relation between the data taken from the two different surveypro. Surveypro is not relational but a relation can be established between data downloaded from different surveypro.

Groups of students entering shared responses
Usually the response provided by a student is NEVER displayed to the other students. All is covered by a kind of privacy. In spite of this, if a group of students is supposed to fill the same surveypro several times within the frame of the same research, each member of the group should be allowed to see, edit and, eventually, delete responses from each other member of his/her same group. This goal is reached making groups at course or surveypro instance level and providing special permissions (seeotherssubmissions, editotherssubmissions, deleteotherssubmissions) to students. Students of group A will never "see" the responses from students of group B but within the same group each student will be allowed to see/edit/delete the responses from people of his/her own group.

Responses can be searched and exported.

A few more features are available at instance level like:
- Branches increase pages
- Allow Save/Resume
- Preserve history
- Anonymous reponses
- Custom style sheet
- Maximum attempts
- Notify role
- More notifications
- Thanks web page
- Deadline of risky modification session
Help buttons in the module provide a quick explanation of each of those settings.

What is really missing is the automatic conversion of current Moodle core survey module. I wonder if people do really request this feature.

Known bugs
If a long text using the html editor has a parent item, it is NOT disabled while the answer to the parent item does not match the condition required by the child. MDL-25067: mform editor element can not be disabled with mform->disabledIf method.

'Multiselect' and 'long text' do NOT get indented in the form.
MDL-28194: I am in trouble assigning syles to some mform element.

A not mandatory 'multiselect' item is NOT disabled while the "No answer" checkbox is selected.
MDL-43704: mform multiselect element is not disabled by the grouped checkbox (as many of other mform elements do)

Surveypro icons are missing from head/admin/plugins.php overview report.
MDL-45723: uniformize the handling of svgs in resolve_image_location() calls.

Thanks to Joseph Rezeau for the revision of the text and English.