# LifeLine: A Web-Based Blood Donor Finder and Emergency Request Coordination System

> **Draft for team review, 2026-11-17 (M4.1).** Citation style: APA 7th edition. Every cited source is listed and checked in [source-matrix.md](source-matrix.md) (DEC-008).
> **[VERIFY]** marks a claim that still needs a source or local data from the team. **[TEAM]** marks a detail only the team can fill in, such as names, the university and the adviser.
> The title is a working title. Change it if the instructor uses a different title pattern.

---

## CHAPTER 1: THE PROBLEM AND ITS BACKGROUND

### Introduction

Blood transfusion saves lives in many situations. In low- and middle-income countries it is used more often to manage complications of pregnancy and severe childhood anaemia, while in high-income countries it supports surgery, massive trauma and cancer treatment (World Health Organization [WHO], 2026). The supply comes from people who donate. WHO (2026) reports that about 120.4 million blood donations are collected worldwide each year, but the supply is uneven. The median donation rate is 28.9 donations per 1,000 people in high-income countries and only 4.5 per 1,000 in low-income countries, and 55 countries report fewer than 10 donations per 1,000 people. WHO (2026) also states that an adequate and reliable supply of safe blood depends on a stable base of regular, voluntary, unpaid donors. WHO and the International Federation of Red Cross and Red Crescent Societies (2010) describe voluntary non-remunerated donors as the foundation of a safe, sustainable blood supply.

In the Philippines, the National Blood Services Act of 1994 (Rep. Act No. 7719) makes it state policy to promote and encourage voluntary blood donation, and to provide an adequate, safe, affordable and equitable supply of blood. Reaching that goal has been difficult. The Department of Health (DOH) set a target of one million blood units a year, but about 930,000 units were collected in 2016, roughly 70,000 short (Gumatay, 2017). Seventeen percent of the 2016 donations were replacement donations, and one percent were paid (Gumatay, 2017). Antonio (2023) notes that in 2009 about one in four blood units came from replacement donation, meaning blood given by family and friends in exchange for stored blood a patient has used. He also notes that the COVID-19 pandemic made low voluntary donation rates worse.

When a patient needs blood and the blood bank cannot supply it at once, families often have to find donors themselves. Many turn to social media. In a study of blood donation accounts on Twitter, Abbasi et al. (2018) found that seven accounts received more than 900 donation requests a day on average. Iparraguirre-Villanueva et al. (2022) likewise observe that blood requests are often posted on social networks during shortages. Public posts like these reach people whether or not they are compatible or nearby, and they can expose the patient's and the donor's personal details. The Data Privacy Act of 2012 (Rep. Act No. 10173) classifies information about a person's health as sensitive personal information and requires that personal data be adequate and not excessive for its purpose.

Mobile and web technology offers a more organized option. Li et al. (2023) reviewed 37 studies on blood donation apps and found that the largest group (44.1%) was about using apps to find blood donors and blood centers. In their pilot survey of 952 young adults, 73% had favorable views of a blood donation app. Local developers have built donor finder apps as well, such as Dug-Uhay in Nueva Ecija, which uses location-based services to find willing donors (Olipas & Villanueva, 2019). However, a review of 169 free blood donation apps found that about half required no authentication, about 60% did not alert users to donation requests, and most gave no donation guidance (Ouhbi et al., 2015).

This study developed **LifeLine**, a mobile-first web application that connects patients and their families (requesters), verified volunteer blood donors and administrators. Through LifeLine, a requester submits an emergency blood request and receives a unique reference number. The system then suggests compatible, available and nearby potential donors and notifies them. Donors accept or decline, and the requester tracks the request until it is fulfilled. LifeLine protects donor privacy by showing only approximate locations and by revealing contact details only after a donor accepts. It never decides medical compatibility or eligibility. Healthcare professionals confirm both before any donation.

### Background of the Study

LifeLine was developed as the final project for ITE-18 at **[TEAM: university name, college and department]**. The system is set in Butuan City. Its sample data, default map center and hospital list are for Butuan City.

**[VERIFY: local situation.]** No verified data on blood demand, shortages or donor search delays in Butuan City or the Caraga Region has been found yet. The team should add one or two sentences here with a citable local source, for example an interview with a hospital blood bank or the local Philippine Red Cross chapter, or an official DOH Caraga report. Until then, this section should not state any local figure.

The Philippine Red Cross (PRC, n.d.) reports that its donations account for almost half of the nation's blood supply. It also states that a healthy individual may donate every three months. This three-month interval is the rule LifeLine uses when it decides whether a donor can be suggested again. Research on Filipino donors suggests that access matters as much as willingness. In a survey of 260 Metro Manila adults, respondents had adequate knowledge of blood donation and were motivated mainly because relatives and friends needed blood (Mappala et al., 2023). Antonio (2023) adds that only about one in three of those respondents had ever donated. They rated the geographic proximity of the facility and the availability of communication lines highly as motivators. Antonio (2023) argues that service-improving strategies, not only appeals to altruism, are needed to raise voluntary donation.

These findings shaped LifeLine's design. A requester should not have to rely on public posts. A donor should be found by blood type compatibility and distance. Communication between requester and donor should happen inside one system, and the donor's privacy should be protected throughout.

### Conceptual Framework

The study follows the Input–Process–Output (IPO) model.

| Input | Process | Output |
|-------|---------|--------|
| **Knowledge requirements:** blood donation process and rules (red-cell ABO/Rh compatibility, 3-month donation interval); Philippine laws on blood services (RA 7719) and data privacy (RA 10173); existing blood donor systems and related studies.<br>**Software requirements:** Laravel 12 (PHP 8.2), MariaDB/MySQL, Bootstrap 5, plain JavaScript, Leaflet and OpenStreetMap, browser Geolocation API, Chart.js, SMTP email.<br>**Hardware requirements:** laptop or desktop computer (development and administration), Android or iOS smartphone with a web browser (donors and requesters). | **Development** using an iterative, milestone-based Software Development Life Cycle: requirements analysis, database and system design, implementation of each feature, and testing.<br>**Testing:** automated unit and feature tests, functional, database, API, input validation, login and registration, duplicate reference number, emergency request and donor matching test cases.<br>**Evaluation:** usability with the System Usability Scale (Brooke, 1996). | **LifeLine:** a mobile-first, installable web-based blood donor finder and emergency request coordination system with role-based accounts, donor management, blood requests with reference numbers and tracking, donor matching, a privacy-preserving donor map, emergency notifications and an admin dashboard. |

*Figure 1. Conceptual framework of the study.* **[TEAM: redraw as a three-box IPO diagram in the manuscript.]**

### Statement of the Problem

The study aimed to develop LifeLine, a web-based blood donor finder and emergency request coordination system that reduces the delay in finding potential blood donors in Butuan City.

Specifically, it sought to answer the following questions:

1. How can the system register and securely authenticate donors, requesters and administrators, verify email addresses, and limit each user to the functions of their role?
2. How can donors keep their blood type, medical information, location and availability up to date, and record their donation history?
3. How can requesters submit an emergency blood request (blood type, units, urgency and hospital) that receives a unique reference number, and track its status by that number?
4. How can the system suggest compatible, eligible and nearby potential donors for a request, and let donors accept or decline it?
5. How can the system show nearby available donors on a map without exposing their exact locations?
6. How can the system notify donors and requesters of new requests, acceptances and status changes?
7. How can administrators verify donors, manage users, requests and hospitals, and view statistics and reports?
8. How can a normalized relational database store the system's records while enforcing data integrity, including the uniqueness of reference numbers?
9. How can a REST API connect the frontend and backend for request tracking, donor matching, the donor map, notifications and statistics?
10. How functional and usable is the developed system, as measured by test cases and the System Usability Scale?

### Objectives of the Study

#### General Objective

To develop LifeLine, a web-based blood donor finder and emergency request coordination system that connects requesters, verified volunteer blood donors and administrators, so that potential blood donors can be found faster and in a more organized and private way.

#### Specific Objectives

Each specific objective matches one implemented feature of the system. The right-hand columns are for the team's traceability and are not part of the manuscript.

| No. | Specific objective | Implemented feature (milestone) |
|-----|--------------------|---------------------------------|
| 1 | To develop a user account module that lets donors and requesters register in three steps, log in with an email address or mobile number, verify their email address and reset a forgotten password, with hashed passwords, role-based access for donors, requesters and administrators, and administrator verification of donor accounts. | Authentication, roles and app shell (M1.3) |
| 2 | To develop a donor management module in which donors maintain their profile and medical information (blood type, birthdate, weight and city), set their availability, choose whether to share an approximate location, and record their donation history. | Donor management and availability (M2.1) |
| 3 | To develop a blood request module that lets requesters submit an emergency request with blood type, number of units (1 to 10), urgency and hospital; issues a unique reference number for each request; records every status change in a request history; and lets anyone track a request's status by its reference number. | Blood requests, reference numbers and tracking (M2.2) |
| 4 | To implement a rule-based donor matching feature that suggests up to ten potential donors per run who are red-cell ABO/Rh compatible, verified, available, located, and have not donated within the last three months, ranked by distance from the hospital, and that lets donors accept or decline a request, sharing contact details only after acceptance. | Donor matching (M2.3) |
| 5 | To develop a donor map that uses the browser's location (or a default location when permission is denied) to show nearby available donors by blood type, with approximate locations rounded to about one kilometer, distances in whole kilometers, and hospital markers. | Donor map and geolocation (M2.4) |
| 6 | To implement emergency notifications that send in-app alerts and email to matched donors and to requesters about new matches, acceptances and status changes, with an unread count and a user setting for email. | Emergency notifications (M2.5) |
| 7 | To develop an administrator dashboard for verifying donors; managing users, donors, blood requests (including status overrides with a recorded reason) and hospitals; and viewing statistics, charts and exportable reports. | Admin dashboard and reports (M2.6) |
| 8 | To design and implement a normalized relational database in MariaDB/MySQL with primary and foreign keys, UNIQUE and CHECK constraints, and sample records for users, donors, administrators, hospitals, blood requests, donor matches, notifications and request history. | Architecture, ERD and SQL database (M1.2) |
| 9 | To design a versioned REST API (`/api/v1`) that serves request tracking, potential donor lists, donor map data, notifications and administrator statistics to the application's own screens. | REST API (M2.2–M2.6) |
| 10 | To test the system's functionality through automated tests and documented test cases, and to evaluate its usability using the System Usability Scale. | Test plan and automated tests (M3.1; SUS sessions pending) |

**[TEAM]** Objective 10 is in progress: the automated tests and test case table are done, but the SUS sessions with real participants (USE-01) have not been run yet.

### Scope and Limitations of the Study

**Scope.** LifeLine is a mobile-first web application that can be installed on a phone's home screen and also runs in a laptop browser. It has three kinds of users. *Donors* register their blood type and medical information, set their availability, share an approximate location, and accept or decline requests. *Requesters* (patients, family members or other persons acting for a patient) submit and track blood requests. *Administrators* verify donors and oversee users, requests, hospitals and reports. Donors may also submit requests. The system covers registration and login, donor management, blood requests with unique reference numbers and public tracking, rule-based donor matching, a donor map, in-app and email notifications, an administrator dashboard with reports, a relational database and a REST API. The study is set in Butuan City.

**Limitations.**

1. **No medical decisions.** LifeLine suggests *potential* donors only. Matching uses red-cell ABO/Rh compatibility and information the donor reports about themselves. Final compatibility and donor eligibility are always confirmed by healthcare professionals, and every match screen shows this disclaimer.
2. **Self-reported data.** Blood type, weight, medical information and donation history are entered by donors and are not checked against laboratory or blood bank records. Administrators verify donor accounts manually, and no government ID is uploaded or stored, in keeping with the data minimization principle of the Data Privacy Act (Rep. Act No. 10173).
3. **Simple eligibility rules.** The only time-based eligibility rule is the three-month interval since the donor's last recorded donation (Philippine Red Cross, n.d.). Other screening criteria, such as age, weight, hemoglobin and health history, are left to the blood bank.
4. **Approximate locations.** Donor locations are stored and shown rounded to two decimal places (about one kilometer), and distances are shown in whole kilometers. Distances are straight-line distances, not travel routes.
5. **Notification channels.** Notifications are in-app alerts and email. SMS and web push notifications are not included because Philippine SMS gateways are paid and push notifications add setup risk.
6. **Map tiles need the internet.** The map uses OpenStreetMap's public tile server, which works on a best-effort basis with no guarantee of availability (OpenStreetMap Foundation, n.d.). Lists and other screens still work without the map.
7. **Hospital data.** The hospitals in the sample data are clearly labeled samples. Administrators must add the real hospitals and blood banks of Butuan City before actual use.
8. **No integration with blood banks.** LifeLine does not connect to hospital or blood bank inventory systems. It coordinates people, not blood stocks.
9. **Deployment.** **[TEAM: state whether the system was evaluated on localhost or on a hosted server.]** Security hardening needed for a public deployment is outside the scope of this project.

### Significance of the Study

The study is expected to benefit the following:

- **Patients and their families (requesters).** They can ask for blood through one system instead of public social media posts, get a reference number, and follow the request's status, which can shorten the search for a donor during an emergency.
- **Blood donors.** Donors receive requests that match their blood type and location, decide whether to help, and keep their exact home location and contact details private until they choose to accept.
- **Hospitals and blood banks.** They gain an additional way to reach willing donors when stocks run low, especially for less common blood types. **[VERIFY: confirm with a local hospital or blood bank before stating how they would use it.]**
- **Administrators and blood donation organizers.** The dashboard gives them a view of registered donors, open requests and statistics that can support recruitment and planning.
- **The community of Butuan City.** A more organized donor network supports the national policy of voluntary blood donation under Republic Act No. 7719.
- **Future researchers and IT students.** The study provides a documented, working example of a privacy-aware, location-based system built with free and open technologies, which they can extend, for example with SMS or blood bank integration.

### Definition of Terms

The following terms are defined conceptually (as used in the literature) and operationally (as used in this study).

- **ABO/Rh compatibility.** Conceptually, the matching of donor and recipient blood groups so that the recipient's antibodies do not react with the donor's red cells. For example, people of group O carry both anti-A and anti-B antibodies (Dean, 2005). **[VERIFY: S18 is partly verified. Open the source before keeping this citation.]** Operationally, the fixed table in LifeLine that lists, for each of the eight recipient blood types, which donor red-cell types may be suggested.
- **Administrator.** Operationally, a LifeLine user who verifies donors and manages users, requests, hospitals and reports.
- **Blood request.** Operationally, a record created by a requester for a patient who needs blood. It states the blood type, number of units, urgency and hospital, and moves through the statuses open, donor found, fulfilled, cancelled or expired.
- **Donation interval.** Conceptually, the minimum time between two blood donations. The Philippine Red Cross (n.d.) states that a healthy individual may donate every three months. Operationally, the three-month rule LifeLine uses to leave out donors who donated recently.
- **Donor matching.** Operationally, the rule-based process by which LifeLine selects compatible, verified, available and nearby donors for a blood request and ranks them by distance.
- **Geolocation.** Conceptually, access to geographical location information associated with the hosting device (World Wide Web Consortium, 2026). Operationally, the browser feature LifeLine uses, with the user's permission, to center the donor map and to set a donor's approximate location.
- **Potential donor.** Operationally, a donor whom LifeLine suggests for a request. The person becomes an actual donor only after healthcare professionals confirm compatibility and eligibility.
- **Reference number.** Operationally, the unique code LifeLine gives every blood request, in the form LL-YYMMDD-XXXXXX, which is used to track the request.
- **Replacement donation.** Conceptually, blood donated by a patient's family and friends in exchange for stored blood the patient used (Antonio, 2023).
- **Requester.** Operationally, a LifeLine user who submits a blood request for a patient.
- **REST API.** Operationally, LifeLine's set of web addresses under `/api/v1` that return data in JSON format to the application's screens.
- **Sensitive personal information.** Conceptually, personal information that includes, among others, information about an individual's health (Data Privacy Act of 2012, Rep. Act No. 10173, Sec. 3).
- **System Usability Scale (SUS).** Conceptually, a ten-item questionnaire for measuring perceived usability (Brooke, 1996). **[VERIFY: S16 is partly verified.]** Operationally, the questionnaire used to evaluate LifeLine with participants.
- **Voluntary blood donation.** Conceptually, blood given freely without payment. Voluntary non-remunerated donors are the foundation of a safe, sustainable blood supply (WHO & IFRC, 2010).

---

### References (Chapter 1)

Abbasi, R. A., Maqbool, O., Mushtaq, M., Aljohani, N. R., Daud, A., Alowibdi, J. S., & Shahzad, B. (2018). Saving lives using social media: Analysis of the role of Twitter for personal blood donation requests and dissemination. *Telematics and Informatics, 35*(4), 892–912. https://doi.org/10.1016/j.tele.2017.01.010

Antonio, C. A. T. (2023). Voluntary blood donation among the youth: Time to consider service-improving strategies? *Acta Medica Philippina, 57*(5), 3–4. https://doi.org/10.47895/amp.v57i5.8044

Brooke, J. (1996). SUS: A "quick and dirty" usability scale. In P. W. Jordan, B. Thomas, B. Weerdmeester, & I. L. McClelland (Eds.), *Usability evaluation in industry* (pp. 207–212). CRC Press. https://doi.org/10.1201/9781498710411-35

Data Privacy Act of 2012, Rep. Act No. 10173 (2012) (Phil.). https://lawphil.net/statutes/repacts/ra2012/ra_10173_2012.html

Dean, L. (2005). The ABO blood group. In *Blood groups and red cell antigens* (Chapter 5). National Center for Biotechnology Information. https://www.ncbi.nlm.nih.gov/books/NBK2267/

Gumatay, J. (2017, July 18). DOH promotes a culture of voluntary blood donation as vital measure on emergency preparedness nationwide. *PTV News*. https://ptvnews.ph/doh-promotes-a-culture-of-voluntary-blood-donation-as-vital-measureon-emergency-preparedness-nationwide

Iparraguirre-Villanueva, O., Sierra-Liñan, F., & Cabanillas-Carbonell, M. (2022). Location-based mobile application for blood donor search. *International Journal of Advanced Computer Science and Applications, 13*(4). https://doi.org/10.14569/IJACSA.2022.0130418

Li, L., Valero, M., Keyser, R., Ukuku, A. M., & Zheng, D. (2023). Mobile applications for encouraging blood donation: A systematic review and case study. *Digital Health, 9*, Article 20552076231203603. https://doi.org/10.1177/20552076231203603

Mappala, A. C. A., Alican, C. A. L., Dulay, D. C. T., Mancita, S. C. A., Utanes, B. Y. G., & Clemente, B. M. (2023). Factors affecting voluntary blood donations among adults in Metro Manila, Philippines, as a basis for policy improvement on donor recruitment. *Acta Medica Philippina, 57*(5), 73–81. https://doi.org/10.47895/amp.vi0.4351

National Blood Services Act of 1994, Rep. Act No. 7719 (1994) (Phil.). https://lawphil.net/statutes/repacts/ra1994/ra_7719_1994.html

Olipas, C. N., & Villanueva, E. (2019). Dug-Uhay: A blood donor finder application. *International Journal of Trend in Scientific Research and Development, 4*(1), 757–762. https://doi.org/10.5281/zenodo.7813402

OpenStreetMap Foundation. (n.d.). *Tile usage policy*. Retrieved November 17, 2026, from https://operations.osmfoundation.org/policies/tiles/

Ouhbi, S., Fernández-Alemán, J. L., Toval, A., Idri, A., & Rivera Pozo, J. (2015). Free blood donation mobile applications. *Journal of Medical Systems, 39*(5), Article 52. https://doi.org/10.1007/s10916-015-0228-0

Philippine Red Cross. (n.d.). *Give blood*. Retrieved November 17, 2026, from https://redcross.org.ph/give-blood/

World Health Organization. (2026, June 12). *Blood safety and availability* [Fact sheet]. https://www.who.int/news-room/fact-sheets/detail/blood-safety-and-availability

World Health Organization & International Federation of Red Cross and Red Crescent Societies. (2010). *Towards 100% voluntary blood donation: A global framework for action*. World Health Organization. https://www.who.int/publications/i/item/9789241599696

World Wide Web Consortium. (2026, November 5). *Geolocation* (W3C Candidate Recommendation Draft). https://www.w3.org/TR/geolocation/
