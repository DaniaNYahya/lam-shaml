USE lam_shaml;

INSERT INTO accounts (account_id, full_name, phone, email, password_hash, city, role, status) VALUES
(1, 'مدير النظام', '0590000001', 'admin@lamshaml.com', '$2y$10$f8zQCIODBum.P79TNle/EO17TvgmSCOQAJe8cWCoShu4XRMvM5UU2', 'غزة', 'admin', 'active'),
(2, 'سارة خالد سالم', '0590000002', 'user@example.com', '$2y$10$WRb77z1podhJe8hZTVTD5.1y/Xlywb5XsZnAAixLo41sx9Si64gOa', 'غزة', 'user', 'active'),
(3, 'منظمة عون الإنسانية', '0590000003', 'org@example.com', '$2y$10$0vvpouNV.YIERAcqEW932eDbY/bZxl4MhYfrV4PgvyZ9uxs/7Onme', 'خان يونس', 'organization', 'active');

INSERT INTO reunification_requests (request_id, account_id, request_type, status, priority, description, contact_phone) VALUES
(1, 2, 'missing', 'active', 'high', 'خرج من المنزل ولم يعد، يرتدي قميصاً أزرق ويحمل حقيبة صغيرة.', '0591234512'),
(2, 3, 'found', 'active', 'high', 'تم العثور على شخص مشابه في مركز إيواء ويحتاج للتواصل مع عائلته.', '0592222212'),
(3, 2, 'missing', 'pending', 'normal', 'طفلة فقدت قرب السوق وتلبس معطفاً رمادياً.', '0593333312'),
(4, 3, 'found', 'active', 'normal', 'تم تسجيل طفلة باسم قريب في نقطة إسعاف ميدانية.', '0594444412');

INSERT INTO family_members (request_id, full_name, normalized_name, age, gender, original_city, relationship, health_status, distinctive_marks, registered_by) VALUES
(1, 'أحمد عبد الرحمن سالم', 'احمد عبد رحمن سالم', 34, 'male', 'غزة', 'أخ', 'إرهاق وجفاف بسيط', 'ندبة صغيرة فوق الحاجب', 'سارة خالد سالم'),
(2, 'احمد عبدالرحمن سالم', 'احمد عبدالرحمن سالم', 35, 'male', 'غزة', NULL, 'مستقر', 'ندبة قرب الحاجب', 'منظمة عون الإنسانية'),
(3, 'هدى مصطفى علي', 'هدي مصطفي علي', 9, 'female', 'خان يونس', 'ابنة', 'سليمة', 'شامة قرب اليد اليمنى', 'سارة خالد سالم'),
(4, 'هدا مصطفى على', 'هدا مصطفي علي', 10, 'female', 'خان يونس', NULL, 'سليمة', 'شامة في اليد', 'نقطة إسعاف ميدانية');

INSERT INTO locations (request_id, city, area, last_known_place, current_location, last_seen_date) VALUES
(1, 'غزة', 'الرمال', 'قرب مستشفى الشفاء', NULL, '2026-07-18'),
(2, 'غزة', 'الرمال', 'قرب مستشفى الشفاء', 'مركز إيواء الرمال', '2026-07-19'),
(3, 'خان يونس', 'السوق', 'سوق خان يونس', NULL, '2026-07-17'),
(4, 'خان يونس', 'السوق', 'قرب السوق', 'نقطة إسعاف ميدانية', '2026-07-18');

INSERT INTO match_records (request_id, matched_request_id, name_score, location_score, age_score, gender_score, place_score, total_score, status) VALUES
(1, 2, 92.00, 95.00, 92.00, 100.00, 90.00, 93.40, 'pending'),
(3, 4, 90.00, 88.00, 92.00, 100.00, 82.00, 90.00, 'pending');

INSERT INTO notifications (account_id, message, type, is_read) VALUES
(2, 'تم إنشاء البلاغ رقم 1 وبدأت المطابقة التلقائية.', 'request_created', 0),
(2, 'يوجد تطابق محتمل بنسبة 93% يحتاج إلى مراجعة.', 'match_found', 0),
(3, 'تم إنشاء البلاغ رقم 2 وبدأت المطابقة التلقائية.', 'request_created', 1),
(1, 'تطابق محتمل جديد يحتاج مراجعة بين البلاغين 1 و2.', 'match_review', 0);
