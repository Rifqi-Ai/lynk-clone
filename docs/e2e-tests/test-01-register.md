# E2E Test: Register Flow
# Tests that a new user can register and land on dashboard
# Expected: form submits → /dashboard → user is authenticated

# Step 1: Navigate to register page
# Step 2: Verify form has all required fields (name, email, username, password, password_confirmation)
# Step 3: Fill form with unique credentials
# Step 4: Submit
# Step 5: Verify redirect to /dashboard
# Step 6: Verify user appears in dashboard with their name
# Step 7: Logout to clean up
