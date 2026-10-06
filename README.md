# Mastery Practice

`mod_masterypractice` is a Moodle activity for adaptive practice and spaced review using the native Question Bank and Question Engine.

The activity is built around one question: **what does this learner appear to master today, and what should be reviewed next?**

It is intentionally not a flashcard system and it is not another Quiz skin. Moodle continues to own question rendering, behaviours, response processing and grading. Mastery Practice owns the learning schedule around those questions: what should be shown, when it should return, how evidence changes the learner's estimated mastery, and how teachers can see individual and class-wide gaps.

## Practice Sessions

A learner opens the activity and receives a short Practice Session assembled specifically for that learner. Selection is not uniform random sampling. The selector combines:

- concepts whose review date is due;
- low mastery or low confidence;
- critical concepts;
- unseen questions;
- configured concept weights;
- diversity across concepts;
- a recency penalty that prevents the same question from dominating when equivalent alternatives exist.

Session size, minimum and maximum questions, estimated duration, minimum spacing between sessions, extra sessions and the maximum number of daily reviews are activity settings.

The session itself is a native Moodle question usage (`question_usage_by_activity`) using `deferredfeedback`. Automatically gradable question types are loaded through `question_bank::load_question()`; Mastery Practice does not implement question renderers, behaviours or answer grading.

## Question Bank, categories and tags

Teachers select existing Question Bank content as concepts. A concept can be:

- a question category, optionally including descendants;
- a Moodle question tag.

Each concept has a weight and can be marked critical. A single question can update more than one concept when its category/tag relationships match more than one configured concept.

Question text is never copied into plugin tables. Stable scheduling state references the Question Bank entry, while a session stores the concrete question version used by the Question Engine.

## Mastery is not the grade

Three values are deliberately separated.

**Grade** is the academic value sent to the gradebook according to the configured policy: no grade, best session, session average or final persisted mastery.

**Mastery** is the persisted estimate after observed evidence. It considers correctness, history, streaks, question difficulty and spacing. It is not raw percentage-correct.

**Estimated current mastery** applies knowledge decay when the dashboard is viewed. Decay never rewrites a historic grade and never silently removes course completion. This allows a learner to have, for example, a completed activity and grade 88 while the current estimate has fallen enough that a review is recommended.

Response time is stored as a supporting signal and for reporting, but it is not a direct academic penalty.

## Scheduling strategies

All strategies implement the same `scheduler_interface`.

### Leitner

The traditional box model is used. Correct responses advance a box and incorrect responses return the item to the first box. Box intervals are bounded by the activity minimum and maximum interval settings.

### SM-2

The implementation keeps the standard SM-2 mechanics: response quality is mapped to 0-5, easiness starts at 2.5, the SM-2 easiness update formula is used with a 1.3 floor, failed reviews reset repetitions, and successful intervals progress through 1 day, 6 days and then previous interval × easiness factor. Activity interval limits are applied only after the SM-2 interval has been calculated.

Mastery percentage is kept separate from the SM-2 scheduling variables so an unrelated heuristic is not presented as “SM-2”.

### Adaptive Mastery

Adaptive Mastery is deterministic and testable. Correct answers on harder questions and correct answers after meaningful spacing provide stronger evidence than repeated same-day answers to easy material. Unexpected failure reduces confidence and schedules an earlier return. Repeated successful reviews across days increase consolidation.

No AI is used in mastery calculation.

## Question difficulty

Difficulty is estimated from aggregate performance inside the activity, not from one learner. Until enough observations exist the difficulty is neutral. Once the minimum sample is reached, a Bayesian prior centred on 50% accuracy prevents one small sample from making a question look absurdly easy or difficult.

## Domain map

Learners see each configured concept as one of:

- Mastered;
- In progress;
- Needs practice;
- Review overdue;
- Not yet assessed.

The dashboard shows raw mastery, current mastery after decay, confidence, the next recommended review and a history chart. After a session, the learner sees what was strengthened, what still needs review and the next recommended practice date rather than only a score.

## Teacher dashboard

The teacher dashboard uses persisted/aggregated state rather than replaying every historic response on page load. It shows class mastery by concept and indicators for learners who may deserve attention, including low mastery, repeated failures, overdue review and low participation. These are pedagogical indicators, not automated judgements.

A scheduled task rebuilds class summaries. Question-level difficulty statistics are updated incrementally when sessions are completed.

## Critical concepts

Any selected category or tag can be marked critical and given its own threshold. Completion can therefore require both a high overall mastery and no critical concept below its required level.

This is useful when an average can hide a dangerous gap, such as strong general security performance with weak password or incident-response knowledge.

## Completion

Custom Moodle Completion API rules can require:

- a number of completed Practice Sessions;
- a number of answered questions;
- minimum overall persisted mastery;
- every configured critical concept to meet its threshold.

Completion uses persisted achieved mastery, while dashboards may continue showing a lower current estimate later because of decay. That distinction is intentional.

## Data and scale

The plugin persists compact per-user/per-concept state and per-user/per-question scheduling state. Session answers remain owned by the Moodle Question Engine. Summary tables support teacher dashboards, and indexed due dates avoid scanning full response history when deciding what should be reviewed next.

Course reset removes sessions, mastery, scheduling and learner summaries while retaining activity/concept configuration. Privacy API support exports and deletes the plugin's learner data and also removes owned Question Engine usages. Backup/restore always copies configuration and can restore learner state only when user data is part of the backup; live Question Engine usages are not reused across restores.
