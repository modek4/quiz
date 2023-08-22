# Quiz website PHP/JavaScript/SQL

> **NOTE** 
> The website was created to make it easier for friends from college to learn 💪🏫

### Built on
![Static Badge](https://img.shields.io/badge/PHP_8.2.0-blue)
![Static Badge](https://img.shields.io/badge/MariaDB_10.4.27-green)
![Static Badge](https://img.shields.io/badge/jQuery_3.6.3-orange)
![Static Badge](https://img.shields.io/badge/SASS_1.8.26-pink?color=F62ED5)
##### More
![Static Badge](https://img.shields.io/badge/Chart.js_3.9.1-yellow?color=E9F62E)
![Static Badge](https://img.shields.io/badge/Font_Awesome-blue?color=2EC1F6)

## Table of Contents
### [Main assumptions](#main-assumptions-1)
### [Installation](#installation-1)
### [Quiz](#quiz-1)
1. [Main menu](#main-menu)
2. [Showing questions](#showing-questions)
3. [Report question](#report-question)
4. [Check question](#check-question)
5. [Login page](#login)
### [Admin panel](#admin-panel-1)
1. [Main menu](#admin-panel--main)
2. [Reports questions](#admin-panel--reports)
3. [Manage menu](#admin-panel--manage)
4. [Users list](#admin-panel--users)
5. [User card](#admin-panel--users-card)
### [Summary](#summary-1)

## Main assumptions
<a name="main-assumptions"></a>
- 🌐 **User Accessibility**: Users can use the website on different devices - *maximum one device at a time* - the website is fully responsive.
- 💻 **User Engagement**: The appearance of the site is as simple as possible so that users focus on solving quizzes, without unnecessary distractions.
- 🚀 **Scalability**: The application is built to work optimally with the database and gives the possibility of further development.
- ⚡ **Performance**: The website requires a permanent connection to the network of at least 3G standard for proper operation.
- 🌐 **Cross-Browser Compatibility**: Tested on: *Chrome*, *Opera*, *Firefox*, *Brave*, *Blisk*, *Edge*, *Safari*
- 🔒 **Data Validation**: Multiple content validation protections for the user.
- 📊 **Real-Time Results**: Instant access to information after saving data.
- 👀 **User Tracking**: User validation tracking to match questions, user activity, and study frequency.
- 🌍 **Multilingualism**: Possibility to translate the interface into any language saved in json format. Currently available: *Polish*, *English*
- 👑 **Admin Panel**: The panel allows you to control all the content on the website.

**[⬆ back to top](#table-of-contents)**

## Installation
**Required technologies**:

| PHP   | MariaDB | Jquery | SASS   | Chart.js | Font Awsome                      |
| ----- | ------- | ------ | ------ | -------- | -------------------------------- |
| 8.2.0 | 10.4.27 | [3.6.3](https://releases.jquery.com/jquery/)  | 1.8.26 | [3.9.1](https://cdnjs.com/libraries/Chart.js/3.9.1)    | [link](https://fontawesome.com/) | 


<a name="installation--main"></a><a name="1.1"></a>
- [1.1](#installation--main) **Main**: How to install the website on your own www server?
	- We move the entire "quiz" folder to the main directory of our website. Access to the website is available via the link: {domain}/quiz
 	- Complete the information in the **quiz/config.php** file

🚩 **ATTENTION**
No .htaccess file attached

<a name="installation--database"></a><a name="1.2"></a>
- [1.2](#installation--database) **Database**: In the "/quiz" directory there are files ready for database import:
	- **quiz/sample-clear.sql** - clean database without content with the structure described.
 	- In **quiz/db/connect.php** change your database login details.

🚩 **ATTENTION**
The administrator account<br/>E-mail: admin@admin.com<br/>Password: admin

**[⬆ back to top](#table-of-contents)**

## Quiz

### 📋Main menu
<a name="main-menu--overall"></a><a name="2.1"></a>
- [2.1](#main-menu--overall) In the main menu, the user will find all the options available. The administrator or moderator will also see his management menu. */index.php*

![1](https://github.com/modek4/quiz/assets/85760836/9d2843b4-8a54-481d-ba81-5fddd2f50af3)

**[⬆ back to top](#table-of-contents)**

<a name="main-menu--quiz-selection"></a><a name="2.1.1"></a>
#### 📝Quiz selection
- [2.1.1](#main-menu--quiz-selection) The menu allows you to search for a quiz or select one from the list. */db/subject_list.php*
- After selecting the subject, we get the option to choose the number of questions:
	- 1+ (After answering one question, another one appears)
 	- 10
  	- 20
  	- 40
  	- ??? (Any number of questions entered by the user)
- Below there is a button to call the test, under which the test execution time, result and the ability to save the result are displayed. */db/save_score.php*

**[⬆ back to top](#table-of-contents)**

<a name="main-menu--notification"></a><a name="2.1.2"></a>
#### 🔔Notifications
- [2.1.2](#main-menu--notification) The user is informed about various changes on the website or about his own actions. It can also delete notifications. */db/notifications.php*

**[⬆ back to top](#table-of-contents)**

<a name="main-menu--scores"></a><a name="2.1.3"></a>
#### 🏆Scores
- [2.1.3](#main-menu--scores) The results are saved to the database and presented in the table. */db/show_score.php*
- Detailed information after opening the selected result displays the solved test and additional information such as:
	- The total number of answers for a given subject
 	- Average time for this subject
  	- Average score for this subject
  	- The result of the test
  	- The time of the test
  	- Answer correctness graph

**[⬆ back to top](#table-of-contents)**

<a name="main-menu--quiz-settings"></a><a name="2.1.4"></a>
#### ⚙️Settings
- [2.1.4](#main-menu--quiz-settings) Settings allow you to change the way questions are displayed, enable learning analysis, check your activation code, and access semesters. */db/show_settings.php*
- Next to the settings button, we have a button to change the theme. */db/darkmode.php*

**[⬆ back to top](#table-of-contents)**

<a name="main-menu--showing-questions"></a><a name="2.2"></a>
### 🔍Showing questions
- [2.2](#main-menu--showing-questions) The display of the test depends on the selected settings. Numerically, randomly, or according to analytics. */db/show_quiz.php*
- **Question tiles contain**:
	- *question number*. *question text* (*number of correct answers*)
 	- Possible addition to the question (program code, photo, etc.)
  	- Answers
  	- Button to report a question

![2](https://github.com/modek4/quiz/assets/85760836/07d31de8-fc8f-423f-9276-b73a277ad971)

**Numeric**: The questions are displayed sequentially.

**Random**: The questions are displayed in random order.

**According to analytics**: Information about each question is retrieved relative to the user's previous test performances, and the formula is used to calculate the score for each question according to which the user gets questions with which he had problems before. In addition, at the top of the quiz we see a summary of our learning.

**[⬆ back to top](#table-of-contents)**

<a name="main-menu--report-question"></a><a name="2.3"></a>
### 🔨Report question
- [2.3](#main-menu--report-question) You can report a question using the button in the lower right corner. Marking the answer is not obligatory. */db/report_question.php*

![3](https://github.com/modek4/quiz/assets/85760836/6043d033-f666-4414-9030-77e860ffa559)

**[⬆ back to top](#table-of-contents)**

<a name="main-menu--check-question"></a><a name="2.4"></a>
### ✅Check question
- [2.4](#main-menu--check-question) Checking the answer question takes place after selecting the answer by getting information from the database. The score is updated at this point. Depending on the number of correct answers in a question, the score is changed accordingly. */db/quiz_correct.php*
- With each answer, the file responsible for analytics and score saving process file, is updated. */db/analytic_change.php*

**[⬆ back to top](#table-of-contents)**

### 🔒Login
<a name="login--login"></a><a name="2.5"></a>
- [2.5](#login--login) 🔏**The login system** allows you to protect against unauthorized access to the website. */login.php*
- Login verifies:
	- Email
	- Password
	- IP
	- Device

🤚 These data allow you to check whether the account is used by single user

<a name="login--register"></a><a name="2.5.1"></a>
- [2.5.1](#login--register) 📝**The register system** form filling with e-mail, password and activation code allows the user to create an account. */register.php*

<a name="login--forgot-password"></a><a name="2.5.2"></a>
- [2.5.2](#login--forgot-password) 🔑**Forgot Password** gives the option to change password after providing the following data: e-mail address, new password and the code used for registration. */reset_password.php*

<a name="login--faq"></a><a name="2.5.3"></a>
- [2.5.3](#login--faq) ❓**FAQ** is place the most frequently asked questions and answers to them. */login.php*

**[⬆ back to top](#table-of-contents)**

## 👑Admin panel
<a name="admin-panel--overall"></a><a name="3.1"></a>
- [3.1](#admin-panel--overall) In the manage menu, the admin will find all the options available to moderate the website. *admin/index.php*

### 📋Main menu
<a name="admin-panel--main"></a><a name="3.2"></a>
- [3.2](#admin-panel--main) **The main** panel allows you to check the current website statistics, such as: quiz statistics, user status or the number of questions and reports. 

![4](https://github.com/modek4/quiz/assets/85760836/425b4e08-1ffc-4e90-b953-95b4097306d0)

**[⬆ back to top](#table-of-contents)**

### 📊Reports
<a name="admin-panel--reports"></a><a name="3.3"></a>
- [3.3](#admin-panel--reports) **The reports** tab allows you to view reports and perform the following actions:
	- Update the reported question *admin/db/update_report_question.php*
	- Reject the reported question *admin/db/decline_report_question.php*
 	- Remove the reported question *admin/db/remove_report_question.php*
  	- Refresh the reported question *admin/db/reload_report_question.php*
  	- Copy the reported question to clipboard
  	- Add and remove answers
  	- Change correct answers

![5](https://github.com/modek4/quiz/assets/85760836/285a17b2-5908-4d73-a193-382d0d04f8e9)

ℹ A solid green border indicates the selected correct answer. <br/>ℹ Divided into two colors, it means that the user reported question the correct answer.<br/>ℹ A full border with an accent color indicates the answer that the user believes is correct.

**[⬆ back to top](#table-of-contents)**

### 🔧Manage panel
<a name="admin-panel--manage"></a><a name="3.4"></a>
- [3.4](#admin-panel--manage) **The manage** panel allows you to control the current statistics of information on the website, such as:
	- Adding new quizzes *admin/db/quiz_add.php*
 	- Adding additional questions to existing quizzes *admin/db/quiz_add.php*
  	- Quizzes status change *admin/db/quiz_status.php*
  	- Download quizzes *admin/db/quiz_download.php*
  	- Deleting quizzes *admin/db/quiz_delete.php*
  	- Rename quizzes *admin/db/quiz_rename.php*
  	- Adding or removing moderators *admin/db/quiz_mod.php*
  	- Access code management *admin/db/quiz_code.php*
  	- Analytics score management *admin/db/quiz_analytic.php*

![6](https://github.com/modek4/quiz/assets/85760836/c0f329d8-6809-4b4c-8027-0a8af4ac3a6c)

**[⬆ back to top](#table-of-contents)**

### 👥Users list
<a name="admin-panel--users"></a><a name="3.5"></a>
- [3.5](#admin-panel--users) **The users** panel allows you to manage current users using options such as:
	- Reset all user devices *admin/db/reset_devices.php*
 	- Sending a notification to all users *admin/db/send_notification.php*
  	- Changing the limit of allowed devices and IP addresses *admin/db/device_limit.php*

Each user tab allows us to view detailed information about that user. ([3.6](#admin-panel--users-card))

![7](https://github.com/modek4/quiz/assets/85760836/d69fe5e0-19ad-4709-8069-1207e6f1dd88)

**[⬆ back to top](#table-of-contents)**

### 👤User info
<a name="admin-panel--users-card"></a><a name="3.6"></a>
- [3.6](#admin-panel--users-card) **The user card** is a place that gives more detailed information about a given user, such as:
	- Table of saved results from quizzes
	- Graph of average results from the last time
	- A detailed list of all devices with the ability to delete them *admin/db/reset_devices.php*
	- Possibility to change access to user's semesters *admin/db/edit_term.php*
	- Possibility to block or unblock a user account *admin/db/block_user.php*

![8](https://github.com/modek4/quiz/assets/85760836/4db21732-453d-4e63-91ea-8757b1607c75)

**[⬆ back to top](#table-of-contents)**

## Summary
<a name="summary-1"></a><a name="4.1"></a>
- [4.1](#summary-1) **Summary**

🚩 The website is a hobbistic idea, errors are possible

**[⬆ back to top](#table-of-contents)**
