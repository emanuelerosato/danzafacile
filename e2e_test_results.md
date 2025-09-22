🧪 STARTING E2E TESTING PLAN

=== COURSE EDIT PAGE E2E TESTING ===

🎯 TEST AREAS:
1. Page Load & Basic UI
2. Tab Navigation 
3. Form Fields Functionality
4. Schedule Management
5. Dynamic Lists (Equipment/Objectives)
6. Image Upload
7. Form Submission
8. JavaScript Functions
9. Mobile Responsiveness
10. Error Handling

📋 TESTING CHECKLIST CREATED

📋 E2E TEST REPORT - Course Edit Page

🧪 TEST 1: BASIC FUNCTIONALITY
✅ Database: 31 courses available for testing
✅ Server: Running on localhost:8089 
🔄 Auth: 302 redirect (normal - requires login)

📝 MANUAL TESTING INSTRUCTIONS:

1. Navigate to: http://localhost:8089/login
2. Login with admin credentials
3. Go to: http://localhost:8089/admin/courses/1/edit
4. Start systematic testing...

🎯 TESTING CHECKLIST:

🔍 DETAILED TESTING CHECKLIST:

=== A. PAGE LOAD & UI ===
[ ] Page loads without errors
[ ] No JavaScript console errors  
[ ] CSS styles applied correctly
[ ] All tabs visible: Basic, Details, Students, Schedule, Pricing
[ ] Success/error alerts visible if any
[ ] Course status alert shows correct info

=== B. TAB NAVIGATION ===
[ ] "Basic" tab active by default
[ ] Clicking tabs switches content correctly
[ ] Tab highlighting works (rose color for active)
[ ] Alpine.js x-show working for tab content
[ ] Mobile tab scrolling works

=== C. FORM FIELDS - BASIC TAB ===
[ ] Course name field populated with existing data
[ ] Image upload preview works (if course has image)
[ ] Dance type dropdown has correct options
[ ] Level dropdown works
[ ] Age range fields accept numbers
[ ] Max students field works
[ ] Price field accepts decimals

=== D. SCHEDULE MANAGEMENT ===
[ ] Existing schedule slots displayed correctly
[ ] "Aggiungi Orario" button works
[ ] Day dropdown has Italian days
[ ] Time fields work (start/end time)
[ ] Duration calculation automatic
[ ] Room dropdown populated
[ ] Remove schedule slot works (except first one)
[ ] Schedule slots numbered correctly

=== E. DYNAMIC LISTS ===
[ ] Equipment list shows existing items
[ ] "Aggiungi Attrezzatura" button adds new field
[ ] Remove equipment items works
[ ] Objectives list shows existing items  
[ ] "Aggiungi Obiettivo" button adds new field
[ ] Remove objective items works

=== F. FORM SUBMISSION ===
[ ] "Salva come Bozza" button works
[ ] "Salva Modifiche" button works
[ ] Form validation shows errors
[ ] Success message after save
[ ] Data persists after save

=== G. JAVASCRIPT FUNCTIONS ===
[ ] addScheduleSlot() global function works
[ ] removeScheduleSlot() global function works  
[ ] calculateDuration() works on time change
[ ] Console shows module initialization
[ ] No JavaScript errors in console

=== H. RESPONSIVE DESIGN ===
[ ] Mobile layout works (tab scrolling)
[ ] Form fields stack properly on mobile
[ ] Buttons layout correctly on mobile
[ ] All touch targets 44px+ on mobile
[ ] Desktop layout maintains structure

=== I. ERROR HANDLING ===
[ ] Required field validation
[ ] Date/time validation
[ ] Network error handling
[ ] Loading states on form submission


✅ PREREQUISITES CHECK COMPLETE:
• Database: 31 courses available ✅
• Assets: course-edit CSS (16KB) & JS (11KB) compiled ✅  
• Routes: admin.courses.edit route exists ✅
• Server: Running on localhost:8089 ✅

🚀 READY FOR TESTING!

==================================================
🎯 START YOUR E2E TESTING HERE:

1. Open browser: http://localhost:8089/login
2. Login with admin credentials  
3. Navigate to: http://localhost:8089/admin/courses/1/edit
4. Open Developer Tools (F12) Console tab
5. Work through the checklist above systematically
6. Note any issues below:

📝 TEST RESULTS:
[Start documenting issues found here...]

