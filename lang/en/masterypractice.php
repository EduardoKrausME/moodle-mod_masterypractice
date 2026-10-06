<?php
// This file is part of Moodle - http://moodle.org/

$string['pluginname'] = 'Mastery Practice';
$string['modulename'] = 'Mastery Practice';
$string['modulenameplural'] = 'Mastery Practice activities';
$string['pluginadministration'] = 'Mastery Practice administration';
$string['masterypracticename'] = 'Activity name';

$string['masterypractice:addinstance'] = 'Add a Mastery Practice activity';
$string['masterypractice:view'] = 'View Mastery Practice';
$string['masterypractice:attempt'] = 'Start and complete practice sessions';
$string['masterypractice:viewreports'] = 'View Mastery Practice reports';
$string['masterypractice:manageconcepts'] = 'Manage Mastery Practice concepts';

$string['sessionheader'] = 'Practice sessions';
$string['questionspersession'] = 'Questions per session';
$string['minquestions'] = 'Minimum questions';
$string['maxquestions'] = 'Maximum questions';
$string['estimatedminutes'] = 'Estimated session duration (minutes)';
$string['mixconcepts'] = 'Mix concepts in the same session';
$string['allowextra'] = 'Allow extra practice when nothing is due';
$string['minsessioninterval'] = 'Minimum interval between sessions';
$string['maxdailyreviews'] = 'Maximum question reviews per day';

$string['algorithmheader'] = 'Mastery and scheduling';
$string['scheduler'] = 'Strategy';
$string['scheduler_leitner'] = 'Leitner';
$string['scheduler_sm2'] = 'SM-2';
$string['scheduler_adaptive'] = 'Adaptive Mastery';
$string['mininterval'] = 'Minimum review interval';
$string['maxinterval'] = 'Maximum review interval';
$string['decayhalflifedays'] = 'Knowledge decay half-life (days)';
$string['decayhalflifedays_help'] = 'Used only to estimate current mastery for review decisions and dashboards. It never silently lowers a historical grade or removes completion.';

$string['gradeheader'] = 'Grade';
$string['gradepolicy'] = 'Grade policy';
$string['gradepolicy_none'] = 'No grade';
$string['gradepolicy_best'] = 'Best session';
$string['gradepolicy_average'] = 'Average of completed sessions';
$string['gradepolicy_mastery'] = 'Persisted mastery';
$string['grademax'] = 'Maximum grade';
$string['masterygradeexplain'] = 'Grade and mastery are different. Grade is an academic result sent to the gradebook; current estimated mastery may later decrease because of knowledge decay without changing that historical grade.';

$string['notificationheader'] = 'Review notifications';
$string['notifreview'] = 'Notify learners when a review becomes available';
$string['notifcooldown'] = 'Minimum time between review notifications';

$string['conceptsheader'] = 'Question Bank concepts';
$string['conceptsconfiguredafter'] = 'Save the activity, then use Manage concepts to select Question Bank categories and tags, set weights, and mark critical concepts.';
$string['manageconcepts'] = 'Manage concepts';
$string['addconcept'] = 'Add concept';
$string['editconcept'] = 'Edit concept';
$string['deleteconcept'] = 'Delete concept';
$string['deleteconceptconfirm'] = 'Delete this concept and its derived learner mastery state?';
$string['concepttype'] = 'Source type';
$string['concepttype_category'] = 'Question category';
$string['concepttype_tag'] = 'Question tag';
$string['conceptsource'] = 'Question Bank source';
$string['includesubcategories'] = 'Include subcategories';
$string['conceptweight'] = 'Weight';
$string['criticalconcept'] = 'Critical concept';
$string['criticalthreshold'] = 'Critical mastery threshold';
$string['noconcepts'] = 'No concepts have been configured yet.';
$string['invalidconceptsource'] = 'The selected source is not available in this course Question Bank.';
$string['duplicateconcept'] = 'This concept is already configured.';

$string['completionheader'] = 'Mastery Practice completion';
$string['completionsessions'] = 'Require completed practice sessions';
$string['completionquestions'] = 'Require answered questions';
$string['completionmastery'] = 'Require overall mastery of at least';
$string['completioncritical'] = 'Require every critical concept to meet its threshold';
$string['completiondetail:sessions'] = 'Complete at least {$a} practice sessions';
$string['completiondetail:questions'] = 'Answer at least {$a} questions';
$string['completiondetail:mastery'] = 'Reach at least {$a}% overall persisted mastery';
$string['completiondetail:critical'] = 'Meet the threshold for every critical concept';

$string['adminmininterval'] = 'Administrative minimum review interval';
$string['adminmininterval_desc'] = 'Activity settings cannot schedule reviews more frequently than this.';
$string['adminmaxinterval'] = 'Administrative maximum review interval';
$string['adminmaxinterval_desc'] = 'Activity settings cannot schedule reviews farther apart than this.';
$string['adminmaxdailyreviews'] = 'Administrative maximum daily reviews';
$string['adminmaxdailyreviews_desc'] = 'Maximum number of question reviews an activity may allow per learner per day.';
$string['difficultysamples'] = 'Question difficulty minimum sample';
$string['difficultysamples_desc'] = 'Until an item reaches this many observations in the activity, difficulty remains neutral.';

$string['domainmap'] = 'Your mastery map';
$string['currentmastery'] = 'Estimated current mastery';
$string['persistedmastery'] = 'Persisted mastery';
$string['confidence'] = 'Confidence';
$string['nextreview'] = 'Next review';
$string['practicenow'] = 'Available now';
$string['startpractice'] = 'Start practice';
$string['continuesession'] = 'Continue current session';
$string['todaypractice'] = 'Today\'s practice';
$string['questioncount'] = '{$a} questions';
$string['estimatedtime'] = '≈ {$a} minutes';
$string['state_mastered'] = 'Mastered';
$string['state_progress'] = 'In progress';
$string['state_practice'] = 'Needs practice';
$string['state_overdue'] = 'Review overdue';
$string['state_unassessed'] = 'Not yet assessed';
$string['noquestionsavailable'] = 'There are no automatically gradable Question Bank questions available for the configured concepts.';
$string['nothingdue'] = 'Nothing is due yet. The next recommended review is {$a}.';
$string['sessiontoosoon'] = 'Another practice session can start after {$a}.';
$string['dailylimitreached'] = 'The daily review limit has been reached.';
$string['sessioninprogress'] = 'You already have a practice session in progress.';
$string['finishpractice'] = 'Finish practice';
$string['sessioncompleted'] = 'Session completed';
$string['score'] = 'Session score';
$string['strengthened'] = 'You strengthened';
$string['needsreview'] = 'Needs review';
$string['recommendednext'] = 'Next recommended practice';
$string['backtoactivity'] = 'Back to activity';
$string['evolution'] = 'Mastery evolution';

$string['teacherdashboard'] = 'Teacher dashboard';
$string['classmastery'] = 'Class mastery';
$string['learnersattention'] = 'Learners who may need attention';
$string['indicatornote'] = 'These are pedagogical indicators based on participation and mastery evidence, not automated judgements.';
$string['indicator_lowmastery'] = 'Low mastery';
$string['indicator_overdue'] = 'Overdue reviews';
$string['indicator_failures'] = 'Repeated failures';
$string['indicator_lowparticipation'] = 'Low participation';
$string['usercount'] = 'Learners';
$string['overduecount'] = 'Overdue';
$string['lowcount'] = 'Low mastery';
$string['lastsummaryupdate'] = 'Summary last updated: {$a}';
$string['summarynotready'] = 'Class summaries have not been built yet. They are refreshed by scheduled task.';
$string['viewdetails'] = 'View details';
$string['conceptdetails'] = 'Concept details';

$string['eventpracticesessionstarted'] = 'Practice session started';
$string['eventpracticesessioncompleted'] = 'Practice session completed';
$string['eventmasterylevelreached'] = 'Mastery level reached';
$string['taskrebuildsummaries'] = 'Rebuild Mastery Practice class summaries';
$string['tasksendreviewnotifications'] = 'Send Mastery Practice review notifications';

$string['messageprovider:reviewavailable'] = 'Review availability';
$string['reviewmessagesubject'] = 'Mastery Practice review available';
$string['reviewmessagebody'] = 'A review is available in "{$a->activity}". Open the activity when you are ready to practise.';
$string['reviewmessagesmall'] = 'A Mastery Practice review is available in {$a}.';

$string['errorminmaxquestions'] = 'Minimum questions cannot be greater than maximum questions.';
$string['errorquestionspersession'] = 'Questions per session must be between the configured minimum and maximum.';
$string['errorintervalorder'] = 'Minimum review interval must be smaller than or equal to the maximum interval.';
$string['erroradminmininterval'] = 'The review interval is below the site administrative minimum.';
$string['erroradminmaxinterval'] = 'The review interval is above the site administrative maximum.';
$string['erroradminmaxdaily'] = 'The daily review limit is above the site administrative maximum.';
$string['errorpercent'] = 'Enter a percentage from 0 to 100.';
$string['errorpositive'] = 'Enter a value greater than zero.';

$string['privacy:metadata:masterypractice_cstate'] = 'Stores the learner\'s persisted mastery state for each configured concept.';
$string['privacy:metadata:masterypractice_qstate'] = 'Stores spaced-review scheduling state for each learner and question bank entry.';
$string['privacy:metadata:masterypractice_sessions'] = 'Stores learner practice session summaries.';
$string['privacy:metadata:masterypractice_squestions'] = 'Stores which question versions were presented and the resulting score signals.';
$string['privacy:metadata:masterypractice_history'] = 'Stores mastery snapshots used for learner progress history.';
$string['privacy:metadata:masterypractice_usummary'] = 'Stores a compact per-learner activity summary.';
$string['privacy:metadata:userid'] = 'The learner user ID.';
$string['privacy:metadata:mastery'] = 'Persisted mastery estimate.';
$string['privacy:metadata:confidence'] = 'Confidence in the mastery estimate.';
$string['privacy:metadata:lastreview'] = 'Time of the last review.';
$string['privacy:metadata:nextreview'] = 'Scheduled next review time.';
$string['privacy:metadata:responsetime'] = 'Observed response duration used as an auxiliary signal and report value.';
$string['privacy:metadata:session'] = 'Practice session data.';
$string['privacy:metadata:question'] = 'Question Bank identifiers and result evidence for a presented item.';

$string['resetuserdata'] = 'Delete Mastery Practice learner data';
$string['indicator_noparticipation'] = 'No participation yet';
$string['indicatorstitle'] = 'Indicators';
$string['privacy:metadata:core_question'] = 'The Moodle Question Engine stores the responses submitted during Practice Sessions.';
$string['indicator_decline'] = 'Significant recent mastery decline';
