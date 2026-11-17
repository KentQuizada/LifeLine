# CHAPTER 2: REVIEW OF RELATED LITERATURE AND STUDIES

> **Started draft, 2026-11-17 (M4.2 in progress).** Citation style: APA 7th edition. Only sources checked in [source-matrix.md](source-matrix.md) are cited (DEC-008).
> **[VERIFY]** marks a claim or source the team must still check. **[TODO]** marks a section that needs more sources before M4.2 can be called done.
> Philippine theses usually want several sources per section. This draft uses every verified source we have so far. The team should add more and check each one in the source matrix first.

This chapter presents the literature and studies that guided the development of LifeLine. They are grouped into foreign and local literature, foreign and local studies, and existing systems, followed by a synthesis and the research gap the study addresses.

---

## Related Literature

### Foreign Literature

**Global blood supply.** The World Health Organization (WHO, 2026) reports that about 120.4 million blood donations are collected worldwide each year, but that access to blood is unequal. The median donation rate is 28.9 donations per 1,000 people in high-income countries, 18.2 in upper-middle-income countries, 8.5 in lower-middle-income countries and 4.5 in low-income countries, and 55 countries collect fewer than 10 donations per 1,000 people. The same fact sheet explains that transfusion in low- and middle-income countries is used more often for pregnancy-related complications and severe childhood anaemia. It also states that an adequate and reliable supply of safe blood can be assured by a stable base of regular, voluntary, unpaid donors.

**Voluntary donation as the foundation of supply.** WHO and the International Federation of Red Cross and Red Crescent Societies (2010) set out a global framework for moving toward 100% voluntary blood donation. They describe voluntary non-remunerated blood donors as the foundation of a safe, sustainable blood supply. LifeLine follows this principle: it connects requesters with volunteer donors and has no payment feature.

**Blood group compatibility.** ABO antigens are the most important blood group antigens in transfusion medicine. People of group O carry anti-A and anti-B antibodies, group A people carry anti-B, and group B people carry anti-A (Dean, 2005). **[VERIFY: source S18 is partly verified. A team member must open it once.]** LifeLine's matching applies the red-cell ABO/Rh compatibility rules in a fixed table but leaves the final cross-matching to healthcare professionals.

**Location and mapping technologies.** The W3C Geolocation specification describes a standard way for a web page to get the geographical location of the device it runs on (World Wide Web Consortium, 2026). This lets a web application such as LifeLine find nearby donors without a native app. Leaflet is an open-source JavaScript library for interactive maps that weighs about 40 KB of JavaScript (Leaflet, n.d.). LifeLine displays it with free OpenStreetMap tiles. The OpenStreetMap Foundation (n.d.) provides its standard tile server on a best-effort basis with no service guarantee, and it requires attribution and forbids bulk downloading and offline use. These are the reasons LifeLine shows map attribution and keeps its donor lists usable when the map cannot load.

**[TODO]** Add foreign literature on: (a) emergency notification and alerting in health systems; (b) privacy and location data, for example location obfuscation or approximate locations; (c) the System Usability Scale (Brooke, 1996; Bangor et al., 2008), after the team verifies S16 and S17.

### Local Literature

**The national blood services policy.** The National Blood Services Act of 1994 (Rep. Act No. 7719) declares it state policy to promote and encourage voluntary blood donation and to provide an adequate, safe, affordable and equitable supply of blood and blood products.

**Supply and the donor mix in the Philippines.** In a 2017 press release, the Department of Health set a target of one million blood units a year and reported about 930,000 units collected in 2016, around 70,000 short. Of the 2016 donations, 82% were voluntary, 17% replacement and 1% paid, and women made up only 23% of donors (Gumatay, 2017). The Philippine Red Cross (n.d.) reports that its donations account for almost half of the nation's blood supply, and states that a healthy individual may donate every three months.

**Replacement donation and the need for better services.** Antonio (2023) notes that in 2009 about one in four blood units came from replacement donation, and that the pandemic worsened low voluntary donation rates. Drawing on Mappala et al. (2023), he argues that appeals to humanitarian duty alone are not enough. He calls for service-improving strategies, including attention to the location of donation facilities, communication with donors, and follow-up that turns one-time donors into regular donors.

**Data privacy.** The Data Privacy Act of 2012 (Rep. Act No. 10173) treats information about an individual's health as sensitive personal information (Sec. 3). It also requires that personal data be processed with transparency, for a legitimate purpose, and in proportion to that purpose, meaning adequate and not excessive (Sec. 11). For LifeLine, this supports keeping only approximate donor locations, collecting no government ID, and revealing contact details only after a donor accepts a request.

**[TODO]** Add local literature on: (a) DOH or PRC guidelines on donor eligibility (age and weight sources currently disagree, see the source matrix); (b) local data for Butuan City or the Caraga Region **[VERIFY]**.

---

## Related Studies

### Foreign Studies

**Blood donation apps in app stores.** Ouhbi et al. (2015) reviewed free blood donation apps across four app stores. Of 188 apps found, they analyzed 169. Most ran on Android and focused on helping users find donors. About half required no authentication, about 60% did not alert users to donation events or requests, and most gave no guidance about donation. The authors concluded that better apps with more features are needed to increase the number of volunteer donors.

**Social media as a donor search channel.** Abbasi et al. (2018) studied how people use Twitter to request blood in India. Each of the seven blood donation accounts they studied had more than 35,000 followers on average and received more than 900 donation requests a day. They concluded that automated systems could help reduce the gap between blood donors and people in need.

**Location-based donor search.** Iparraguirre-Villanueva et al. (2022) observed that blood donation requests are often posted on social networks during shortages. They developed a location-based mobile application for finding blood donors, and reported a 39.58% increase in the number of donors and a 53.2% reduction in search time.

**Systematic review of blood donation apps.** Li et al. (2023) reviewed 37 research articles on mobile apps for blood donation and grouped them into five categories. The largest share (44.1%) was about using mobile apps to find blood donors and blood centers. In a pilot survey of 952 participants aged 18 to 39, 73% had favorable perceptions of a blood donation app.

**[TODO]** Add foreign studies on: (a) rule-based donor matching or donor ranking; (b) usability evaluation of health apps using SUS; (c) privacy in location-based health services.

### Local Studies

**Factors affecting voluntary donation.** Mappala et al. (2023) surveyed 260 Metro Manila residents aged 18 to 65 using a descriptive design. Respondents had an adequate perceived level of knowledge about blood donation and were motivated mainly because relatives and friends needed blood. As summarized by Antonio (2023), only about one in three respondents had ever donated. Respondents rated the geographic proximity of the facility, the attitude of staff, and the availability of communication lines highly as motivators.

**Dug-Uhay blood donor finder.** Olipas and Villanueva (2019) developed Dug-Uhay, a mobile blood donor finder that uses location-based services to help locate willing donors. They followed the Spiral Model (planning, risk analysis, engineering and evaluation). IT professionals and residents of a Nueva Ecija municipality assessed the app's usability, effectiveness, efficiency and accessibility, as well as its perceived usefulness and ease of use. It passed both assessments, with suggested improvements.

**Bloodline mobile app design.** Intal et al. (2024) proposed Bloodline, a mobile application meant to make blood donation services easier for both civilians and hospitals. They noted that blood centers try to keep about three days of supply, and that finding donors is hard for people without friends or family who donate regularly, or who need a rare blood type. Most of their 35 survey respondents favored a mobile app for blood donation services. **[VERIFY: the abstract does not name the country. Confirm that this is a Philippine study before keeping it under Local Studies.]**

**[TODO]** Add more local studies, for example theses from CSU or other Caraga universities, or studies in the *Philippine Journal of Science* or *Acta Medica Philippina*.

---

## Existing Systems

The table compares LifeLine with systems described in the reviewed sources. "Not reported" means the source we checked does not mention the feature. It does not mean the system lacks it.

| Feature | Facebook Blood Donations (Budaraju, 2018) | Dug-Uhay (Olipas & Villanueva, 2019) | Location-based donor search app (Iparraguirre-Villanueva et al., 2022) | Bloodline, proposed design (Intal et al., 2024) | **LifeLine** |
|---------|------|------|------|------|------|
| Donor registration | Yes | Not reported | Not reported | Not reported | Yes, with email verification and admin verification of donors |
| Location-based donor search | Yes (nearby needs) | Yes | Yes | Not reported | Yes, ranked by distance |
| Blood type compatibility matching | Not reported | Not reported | Not reported | Not reported | Yes, red-cell ABO/Rh table |
| Donation interval check | Not reported | Not reported | Not reported | Not reported | Yes, 3 months (PRC, n.d.) |
| Notifications to donors | Yes | Not reported | Not reported | Not reported | Yes, in-app and email |
| Request reference number and tracking | Not reported | Not reported | Not reported | Not reported | Yes |
| Approximate locations / contact only after acceptance | Not reported | Not reported | Not reported | Not reported | Yes |
| Admin dashboard and reports | Not reported | Web portal (not reported in detail) | Not reported | Not reported | Yes |
| Platform | Facebook | Mobile app with web portal | Mobile app | Mobile app (proposed) | Installable mobile-first web app |

**[VERIFY]** The Dug-Uhay "web portal" detail comes from a search listing, not the Zenodo abstract. Confirm it in the full paper or remove it. **[TODO]** Read the full texts of S09, S10 and S13 to fill the "Not reported" cells accurately. Consider adding the Philippine Red Cross's own services and one or two apps used in the Philippines, but only after verifying them.

---

## Synthesis

**[DRAFT: complete once the TODO sources are added.]**

The literature agrees that a safe and sufficient blood supply rests on regular, voluntary, unpaid donors (WHO, 2026; WHO & IFRC, 2010). Philippine law adopts the same policy (Rep. Act No. 7719), yet the country has fallen short of its collection target and still depends partly on replacement donation (Antonio, 2023; Gumatay, 2017). Local research suggests that Filipinos know about blood donation and are willing to give when relatives and friends need blood, but that access, proximity and communication affect whether they actually donate (Antonio, 2023; Mappala et al., 2023).

When blood is needed urgently, people often search for donors through social media (Abbasi et al., 2018; Iparraguirre-Villanueva et al., 2022). Mobile and web applications for finding donors are the most studied type of blood donation app (Li et al., 2023), and location-based search has been shown to raise the number of donors found and shorten search time (Iparraguirre-Villanueva et al., 2022). Local developers have built similar tools (Olipas & Villanueva, 2019). However, many existing apps lack authentication, do not alert users to requests, and give no donation guidance (Ouhbi et al., 2015). The reviewed sources also say little about how donor locations and contact details are protected, although Philippine law treats health information as sensitive personal information (Rep. Act No. 10173).

## Research Gap

**[DRAFT]**

Among the works reviewed, none describes, in a single system for a Philippine city, all of the following: (a) verified donor accounts; (b) rule-based red-cell ABO/Rh compatibility matching combined with a donation-interval check; (c) emergency requests with unique reference numbers and status tracking; (d) privacy protection through approximate locations and contact details revealed only after a donor accepts; and (e) notifications and an administrator dashboard. LifeLine was developed to fill this gap for Butuan City. It applies the national three-month donation interval (Philippine Red Cross, n.d.) and the Data Privacy Act's proportionality principle (Rep. Act No. 10173).

**[VERIFY]** This gap statement covers only the sources reviewed so far. Recheck it after the TODO sources are added. It must still follow from the synthesis and must not claim that no such system exists anywhere.

---

### References (Chapter 2)

Abbasi, R. A., Maqbool, O., Mushtaq, M., Aljohani, N. R., Daud, A., Alowibdi, J. S., & Shahzad, B. (2018). Saving lives using social media: Analysis of the role of Twitter for personal blood donation requests and dissemination. *Telematics and Informatics, 35*(4), 892–912. https://doi.org/10.1016/j.tele.2017.01.010

Antonio, C. A. T. (2023). Voluntary blood donation among the youth: Time to consider service-improving strategies? *Acta Medica Philippina, 57*(5), 3–4. https://doi.org/10.47895/amp.v57i5.8044

Budaraju, H. (2018, June 13). Making it easier to donate blood. *Meta Newsroom*. https://about.fb.com/news/2018/06/making-it-easier-to-donate-blood/

Data Privacy Act of 2012, Rep. Act No. 10173 (2012) (Phil.). https://lawphil.net/statutes/repacts/ra2012/ra_10173_2012.html

Dean, L. (2005). The ABO blood group. In *Blood groups and red cell antigens* (Chapter 5). National Center for Biotechnology Information. https://www.ncbi.nlm.nih.gov/books/NBK2267/

Gumatay, J. (2017, July 18). DOH promotes a culture of voluntary blood donation as vital measure on emergency preparedness nationwide. *PTV News*. https://ptvnews.ph/doh-promotes-a-culture-of-voluntary-blood-donation-as-vital-measureon-emergency-preparedness-nationwide

Intal, G. L. D., Alog, J. M., Cahate, K. J. B., Cervero, J. M., & Lopez, S. I. (2024). A study on the proposed design of mobile application for blood donation. In *Proceedings of the 2024 6th World Symposium on Software Engineering (WSSE)* (pp. 105–114). Association for Computing Machinery. https://doi.org/10.1145/3698062.3698076

Iparraguirre-Villanueva, O., Sierra-Liñan, F., & Cabanillas-Carbonell, M. (2022). Location-based mobile application for blood donor search. *International Journal of Advanced Computer Science and Applications, 13*(4). https://doi.org/10.14569/IJACSA.2022.0130418

Leaflet. (n.d.). *Leaflet* (Version 1.9.4) [Computer software]. https://leafletjs.com/

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
