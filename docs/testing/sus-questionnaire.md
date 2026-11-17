# LifeLine usability questionnaire (System Usability Scale)

Usability testing for M3.1. The System Usability Scale (SUS) is a 10-item questionnaire answered on a 5-point scale.

> Source: Brooke, J. (1996). SUS: A "quick and dirty" usability scale. In P. W. Jordan, B. Thomas, B. A. Weerdmeester,
> & I. L. McClelland (Eds.), *Usability evaluation in industry* (pp. 189–194). Taylor & Francis.
> **Team: confirm this reference against a library or publisher copy before citing it in Chapter 3 (DEC-008).**

## How to run it

1. Recruit participants from the target users (for example students who could be donors, and family members of patients).
   Record only a participant number and role, not names (Data Privacy Act).
2. Give each participant the tasks below on a phone or laptop with the demo data (`php artisan migrate:fresh --seed`).
3. Note for each task: completed (yes/no), time, and problems seen. Do not help unless the participant is stuck.
4. Right after the tasks, the participant answers the 10 statements. Do not discuss the answers.

## Tasks

| No. | Task | Account |
|---|---|---|
| T1 | Register as a donor (3 steps). | new |
| T2 | Find available O- donors near you on the map. | requester@lifeline.test |
| T3 | Send an emergency request and write down its reference number. | requester@lifeline.test |
| T4 | Track that request without logging in. | none |
| T5 | Answer a request for your blood type. | donor@lifeline.test |
| T6 | Turn off your availability and your email notifications. | donor@lifeline.test |
| T7 | Verify a waiting donor and see how many requests are open. | admin@lifeline.test |

## Statements

Answer each with 1 = Strongly disagree, 2 = Disagree, 3 = Neutral, 4 = Agree, 5 = Strongly agree.

| No. | Statement | 1 | 2 | 3 | 4 | 5 |
|---|---|---|---|---|---|---|
| 1 | I think that I would like to use LifeLine frequently. | | | | | |
| 2 | I found LifeLine unnecessarily complex. | | | | | |
| 3 | I thought LifeLine was easy to use. | | | | | |
| 4 | I think that I would need the support of a technical person to be able to use LifeLine. | | | | | |
| 5 | I found the various functions in LifeLine were well integrated. | | | | | |
| 6 | I thought there was too much inconsistency in LifeLine. | | | | | |
| 7 | I would imagine that most people would learn to use LifeLine very quickly. | | | | | |
| 8 | I found LifeLine very cumbersome to use. | | | | | |
| 9 | I felt very confident using LifeLine. | | | | | |
| 10 | I needed to learn a lot of things before I could get going with LifeLine. | | | | | |

## Scoring

For each participant:

- Odd statements (1, 3, 5, 7, 9): score − 1.
- Even statements (2, 4, 6, 8, 10): 5 − score.
- Add the ten results and multiply by 2.5. The SUS score is 0–100.

Report the mean SUS score of all participants, with the number of participants. The score is not a percentage.

## Results

_Not run yet. Fill in after testing with real participants; never estimate these numbers._

| Participant | Role | T1–T7 completed | SUS score |
|---|---|---|---|
| P1 | | | |
