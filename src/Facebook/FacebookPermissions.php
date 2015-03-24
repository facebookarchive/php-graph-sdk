<?php
/**
 * Copyright 2014 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace Facebook;

/**
 * Class FacebookPermissions
 * @package Facebook
 */
final class FacebookPermissions
{
    // Basic permissions
    const PUBLIC_PROFILE = 'public_profile';
    const USER_FRIENDS = 'user_friends';
    const EMAIL = 'email';
    // Extended Profile Properties
    const USER_ABOUT_ME = 'user_about_me';
    const USER_ACTIONS_BOOKS = 'user_actions.books';
    const USER_ACTIONS_FITNESS = 'user_actions.fitness';
    const USER_ACTIONS_MUSIC = 'user_actions.music';
    const USER_ACTIONS_NEWS = 'user_actions.news';
    const USER_ACTIONS_VIDEO = 'user_actions.video';
    const USER_ACTIVITIES = 'user_activities';
    const USER_BIRTHDAY = 'user_birthday';
    const USER_EDUCATION_HISTORY = 'user_education_history';
    const USER_EVENTS = 'user_events';
    const USER_GAMES_ACTIVITY = 'user_games_activity';
    const USER_GROUPS = 'user_groups';
    const USER_HOMETOWN = 'user_hometown';
    const USER_INTERESTS = 'user_interests';
    const USER_LIKES = 'user_likes';
    const USER_LOCATION = 'user_location';
    const USER_PHOTOS = 'user_photos';
    const USER_POSTS = 'user_posts';
    const USER_RELATIONSHIPS = 'user_relationships';
    const USER_RELATIONSHIP_DETAILS = 'user_relationship_details';
    const USER_RELIGION_POLITICS = 'user_religion_politics';
    const USER_STATUS = 'user_status';
    const USER_TAGGED_PLACES = 'user_tagged_places';
    const USER_VIDEOS = 'user_videos';
    const USER_WEBSITE = 'user_website';
    const USER_WORK_HISTORY = 'user_work_history';
    // Extended Permissions
    const READ_FRIENDLISTS = 'read_friendlists';
    const READ_INSIGHTS = 'read_insights';
    const READ_MAILBOX = 'read_mailbox';
    const READ_PAGE_MAILBOXES = 'read_page_mailboxes';
    const READ_STREAM = 'read_stream';
    const MANAGE_NOTIFICATIONS = 'manage_notifications';
    const MANAGE_PAGES = 'manage_pages';
    const PUBLISH_PAGES = 'publish_pages';
    const PUBLISH_ACTIONS = 'publish_actions';
    const RSVP_EVENT = 'rsvp_event';
}
