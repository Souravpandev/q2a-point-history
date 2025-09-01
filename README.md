# Q2A Point History Plugin

A comprehensive point history tracking plugin for Question2Answer that allows users to view their point earning history in a beautiful timeline widget. Features include real-time point tracking, admin point history viewer, responsive timeline design, avatar integration, and performance optimizations with minified assets.

## 🚀 Features

### Core Functionality
- **Real-time Point Tracking**: Automatically logs all point-earning activities
- **Beautiful Timeline Widget**: Displays point history in an elegant timeline format
- **Admin Point History Viewer**: Administrators can view any user's complete point history
- **Avatar Integration**: Shows user avatars in the timeline for better visual appeal
- **Responsive Design**: Works perfectly on all devices and screen sizes

### Activity Tracking
- **Questions**: Track points earned from posting questions
- **Answers**: Monitor points from posting answers
- **Comments**: Log points from comment activities
- **Votes**: Track points from voting on content
- **Best Answers**: Record points from selecting best answers
- **Bonus Points**: Log admin-assigned bonus points

### Performance & UX
- **Minified Assets**: Optimized CSS and JS files for faster loading
- **Conditional Loading**: Assets only load when needed
- **Database Optimization**: Efficient queries with JOIN operations
- **SVG Icons**: Modern, scalable icons for better visual appeal
- **Smooth Animations**: Professional transitions and hover effects

### Admin Features
- **Comprehensive Settings**: Control all tracking options
- **User History Viewer**: View any user's point history as admin
- **Data Management**: Cleanup old logs and manage storage
- **Export Functionality**: Download point history data
- **Performance Monitoring**: Track plugin statistics and database health

## 📋 Requirements

- **Question2Answer**: Version 1.8 or higher
- **PHP**: Version 7.0 or higher
- **MySQL**: 5.6 or higher
- **Admin Access**: Required for initial setup and configuration

## 🛠️ Installation

### Method 1: Manual Installation (Recommended)

1. **Download the Plugin**
   - Download the plugin files from the repository
   - Extract the `q2a-point-history` folder

2. **Upload to Q2A**
   - Upload the `q2a-point-history` folder to your `qa-plugin/` directory
   - Ensure the path is: `your-site.com/qa-plugin/q2a-point-history/`

3. **Enable the Plugin**
   - Go to **Admin → Plugins**
   - Find "Q2A Point History Plugin" in the list
   - Click **Enable** to activate the plugin

4. **Configure Settings**
   - Go to **Admin → Plugins → Q2A Point History Plugin Settings**
   - Configure tracking options and widget settings
   - Save your settings

### Method 2: Git Clone

```bash
cd qa-plugin/
git clone https://github.com/Souravpandev/q2a-point-history.git
```

## ⚙️ Admin Settings

### Plugin Configuration

Navigate to **Admin → Plugins → Q2A Point History Plugin Settings**

#### Tracking Options
- **Enable Plugin**: Master switch to enable/disable the plugin
- **Track Questions**: Log points from question posting
- **Track Answers**: Log points from answer posting
- **Track Comments**: Log points from comment activities
- **Track Votes**: Log points from voting actions
- **Track Best Answers**: Log points from best answer selection
- **Track Bonus Points**: Log admin-assigned bonus points

#### Widget Settings
- **Enable Widget**: Show point history widget on pages
- **Widget Display Limit**: Maximum activities to show (default: 10)

#### Performance Settings
- **Max Logs Per User**: Maximum history logs per user (0 = unlimited)
- **Cleanup Days**: Automatically remove logs older than X days

#### Admin Tools
- **Point History Viewer**: Direct link to admin point history page
- **Database Status**: Monitor table health and structure
- **Plugin Statistics**: View usage statistics and data counts

### Database Management

#### Cleanup Actions
- **Cleanup Old Logs**: Remove logs older than specified days
- **Reset All Data**: Permanently delete all point history data
- **Reinstall Plugin**: Fix database structure issues

#### Status Monitoring
- **Table Health**: Check if required tables exist and are healthy
- **Data Counts**: Monitor total activities and user counts
- **Storage Usage**: Track database size and optimization

## 🎯 Usage Guide

### For Users

#### Viewing Your Point History
1. **Widget Display**: Point history appears automatically on pages when enabled
2. **Timeline View**: See your recent point-earning activities in a beautiful timeline
3. **Expand/Collapse**: Click the arrow button to view more or fewer activities
4. **Activity Details**: Each entry shows what you did and how many points you earned

#### Understanding the Timeline
- **Avatar**: Your profile picture or initials
- **Activity**: Description of what you did (e.g., "Posted a question")
- **Points**: Points earned (positive) or lost (negative)
- **Date**: When the activity occurred

### For Administrators

#### Managing User Point History
1. **Access Admin Viewer**: Go to **Admin → Plugins → Q2A Point History Plugin Settings**
2. **Click Point History Viewer**: Opens the admin point history page
3. **Select User**: Choose any user from the dropdown
4. **View Timeline**: See complete point history for the selected user
5. **Search Users**: Use the search box to find specific users quickly

#### Monitoring Plugin Health
1. **Check Database Status**: Monitor table health and structure
2. **Review Statistics**: Track total activities and user engagement
3. **Performance Metrics**: Monitor database size and optimization
4. **Cleanup Maintenance**: Remove old logs to maintain performance

## 🎨 Customization

### CSS Customization
The plugin uses minified CSS files located in `css/point-history.min.css`. For customizations:

1. **Backup Original**: Save a copy of the minified file
2. **Edit Source**: Modify the unminified `css/point-history.css` file
3. **Re-minify**: Use a CSS minifier to create the production version
4. **Update**: Replace the minified file with your custom version

### Widget Placement
The widget automatically appears on all pages when enabled. To customize placement:

1. **Edit Widget File**: Modify `qa-point-history-widget.php`
2. **Adjust Regions**: Change `allow_region()` function
3. **Custom Templates**: Modify `allow_template()` function

### Timeline Styling
Customize the timeline appearance by modifying CSS classes:

- `.timeline`: Main timeline container
- `.timeline-item`: Individual activity items
- `.timeline-avatar`: User avatar display
- `.timeline-header`: Activity information
- `.timeline-action`: Points and activity description

## 🔧 Troubleshooting

### Common Issues

#### Widget Not Displaying
1. **Check Plugin Status**: Ensure plugin is enabled in admin
2. **Verify User Login**: Widget only shows for logged-in users
3. **Check Widget Settings**: Confirm widget is enabled in settings
4. **Clear Cache**: Clear any caching plugins or browser cache

#### Database Errors
1. **Check Table Status**: Go to admin settings and check database status
2. **Reinstall Plugin**: Use the "Reinstall Plugin" button in admin settings
3. **Verify Permissions**: Ensure database user has CREATE/ALTER permissions
4. **Check Q2A Version**: Ensure you're using Q2A 1.8 or higher

#### Performance Issues
1. **Review Log Counts**: Check if you have too many log entries
2. **Adjust Cleanup Settings**: Set appropriate cleanup days
3. **Monitor Database Size**: Check if tables are growing too large
4. **Optimize Queries**: Review database query performance

### Debug Mode

Enable debug mode in Q2A to see detailed error messages:

1. **Edit qa-config.php**: Set `QA_DEBUG_CONSTANT` to `true`
2. **Check Error Logs**: Monitor server error logs for issues
3. **Test Functionality**: Verify each feature works correctly
4. **Disable Debug**: Set debug back to `false` for production

## 📊 Performance Optimization

### Asset Loading
- **Conditional CSS**: Only loads when widget is enabled and user is logged in
- **Minified Files**: Compressed CSS and JS for faster loading
- **Cache Busting**: Uses Q2A version for cache invalidation

### Database Optimization
- **Efficient Queries**: Uses JOIN operations for better performance
- **Indexed Tables**: Proper database indexing for fast queries
- **Cleanup Routines**: Automatic removal of old data
- **Query Limits**: Configurable limits to prevent performance issues

### Memory Management
- **Lazy Loading**: Only loads data when needed
- **Pagination Support**: Handles large datasets efficiently
- **Resource Cleanup**: Proper cleanup of temporary data

## 🔒 Security Features

### Access Control
- **Admin Only**: Point history viewer restricted to administrators
- **User Isolation**: Users can only see their own point history
- **Input Validation**: All user inputs are properly sanitized
- **SQL Injection Protection**: Uses Q2A's built-in query functions

### Data Protection
- **Secure Queries**: All database operations use prepared statements
- **User Permissions**: Respects Q2A's user permission system
- **Audit Trail**: Complete logging of all point activities
- **Privacy Respect**: Only shows data users are authorized to see

## 📈 Future Enhancements

### Planned Features
- **Advanced Analytics**: Detailed point earning analytics and charts
- **Badge Integration**: Automatic badge awards for point milestones
- **Email Notifications**: Point activity notifications
- **API Support**: REST API for external integrations
- **Mobile App**: Dedicated mobile application

### Community Contributions
We welcome contributions from the community! To contribute:

1. **Fork the Repository**: Create your own fork
2. **Create Feature Branch**: Work on new features
3. **Submit Pull Request**: Share your improvements
4. **Follow Guidelines**: Ensure code quality and documentation

## 📞 Support & Documentation

### Getting Help
- **GitHub Issues**: Report bugs and request features
- **Documentation**: Check this README for common solutions
- **Community**: Join Q2A community forums for support
- **Developer**: Contact the development team

### Useful Links
- **Plugin Repository**: [https://github.com/Souravpandev/q2a-point-history](https://github.com/Souravpandev/q2a-point-history)
- **Developer Website**: [https://wpoptimizelab.com/](https://wpoptimizelab.com/)
- **Q2A Official**: [https://www.question2answer.org/](https://www.question2answer.org/)

## 📄 License

This plugin is licensed under the GNU General Public License v3.0 (GPLv3).

```
This program is free software: you can redistribute it and/or
modify it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 👥 Credits

- **Developer**: Sourav Pan
- **Team**: Qlassy Team
- **Website**: [https://wpoptimizelab.com/](https://wpoptimizelab.com/)
- **GitHub**: [https://github.com/Souravpandev](https://github.com/Souravpandev)

---

**Thank you for using Q2A Point History Plugin!** 🎉

For the latest updates and support, visit our [GitHub repository](https://github.com/Souravpandev/q2a-point-history).
