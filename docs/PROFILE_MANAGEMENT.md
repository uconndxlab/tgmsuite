# User Profile Management

## Overview

Users can now manage their own profiles, including updating their personal information and changing their passwords.

## Features

### Profile Information Update
- **Update Name**: Change your display name
- **Update Email**: Change your email address (must be unique)
- **Validation**: Email format validation and duplicate checking

### Password Management
- **Change Password**: Securely update your password
- **Current Password Verification**: Must provide current password to change
- **Password Strength**: Minimum 6 characters required
- **Confirmation**: New password must be entered twice to confirm

## How to Access

1. **Log in** to your account
2. **Click** the "Profile" link in the navigation bar (appears next to Admin link if you're an admin)
3. **Update** your information or change your password

## Profile Update Process

### Updating Profile Information

1. Navigate to `/profile`
2. Edit your **Name** or **Email** in the Profile Information card
3. Click **Update Profile**
4. Success message will appear if update is successful

**Validations:**
- Name and email cannot be empty
- Email must be in valid format (e.g., user@example.com)
- Email must not already be in use by another account

### Changing Password

1. Navigate to `/profile`
2. In the Change Password card, enter:
   - Your **Current Password**
   - Your **New Password** (minimum 6 characters)
   - **Confirm** your new password
3. Click **Change Password**
4. Success message will appear if password is changed

**Validations:**
- All fields are required
- Current password must be correct
- New password must be at least 6 characters
- New password and confirmation must match

## Error Messages

The system provides clear feedback for various scenarios:
- ✓ "Profile updated successfully"
- ✓ "Password changed successfully"
- ✗ "Name and email are required"
- ✗ "Invalid email format"
- ✗ "Email already in use by another account"
- ✗ "All password fields are required"
- ✗ "New passwords do not match"
- ✗ "Password must be at least 6 characters"
- ✗ "Current password is incorrect"

## Security Features

- **Password Hashing**: All passwords are securely hashed using PHP's `password_hash()` with `PASSWORD_DEFAULT`
- **Current Password Verification**: Users must provide their current password before changing to a new one
- **SQL Injection Prevention**: Input sanitization with single quote escaping
- **Email Uniqueness**: Prevents multiple accounts with the same email
- **Session Management**: All profile routes require authentication

## Routes Added

### GET Routes
- `/profile` - View and edit profile page

### POST Routes
- `/profile/update` - Update name and email
- `/profile/change-password` - Change password

## Files Created/Modified

**New Files:**
- `public/templates/profile.html` - Profile management page

**Modified Files:**
- `public/index.php` - Added 3 new routes for profile management
- `public/templates/parts/header.html` - Added Profile link to navigation

## Navigation

The Profile link appears in the main navigation for all logged-in users:
- **Fields** - View your fields
- **Admin** - (Admins only) View all submissions
- **Profile** - Manage your account
- **Log Out** - Sign out

## User Interface

The profile page features a clean, two-column layout:
- **Left Column**: Profile Information (name and email)
- **Right Column**: Password Change Form
- **Bootstrap Cards**: Organized sections with clear headers
- **Icons**: Visual indicators (person icon for profile, etc.)
- **Alert Messages**: Success/error feedback at the top

## Best Practices

1. **Change Default Passwords**: Users should change their password after initial account setup
2. **Use Strong Passwords**: While minimum is 6 characters, longer passwords with mixed characters are recommended
3. **Keep Email Current**: Email may be used for account recovery in the future
4. **Verify Changes**: Check for success messages after making changes

## For Administrators

- Admins can change their own password through the profile page
- The is_admin status cannot be changed through the profile page (database-level only for security)
- Admins have the same profile management capabilities as regular users
