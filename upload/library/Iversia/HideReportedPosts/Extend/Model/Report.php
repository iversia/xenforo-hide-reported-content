<?php

class Iversia_HideReportedPosts_Extend_Model_Report extends XenForo_Model_Report
{
    public function reportContent($contentType, array $content, $message, array $viewingUser = null)
    {
        $reportId = parent::reportContent($contentType, $content, $message, $viewingUser);

        $report = $this->getReportById($reportId);

        $threshold = XenForo_Application::get('options')->HideReportedPostsThreshold;
        $moderatorUsername = XenForo_Application::get('options')->HideReportedPostsThresholdUser;

        if ($threshold && $threshold != 0) {

            if ($this->countUniqueReportsByReportId($report['report_id']) >= $threshold) {

                $alert['user_id'] = 0;
                $alert['username'] = '';

                if ($moderatorUsername) {
                    $moderator = $this->_getUserModel()->getUserByName($moderatorUsername);

                    if ($moderator) {
                        $alert['user_id'] = $moderator['user_id'];
                        $alert['username'] = $moderator['username'];
                    }
                }

                switch ($report['content_type']) {
                    case 'post':

                        $dw = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
                        $dw->setExistingData($report['content_id']);
                        $dw->set('message_state', 'moderated');
                        $dw->save();

                        $post = $this->_getPostModel()->getPostById($report['content_id']);
                        $thread = $this->_getThreadModel()->getThreadById($post['thread_id']);

                        $extra = array(
                            'title' => $thread['title'],
                            'link' => XenForo_Link::buildPublicLink('posts', $post),
                            'threadLink' => XenForo_Link::buildPublicLink('threads', $thread),
                            'reason' => ''
                        );

                        XenForo_Model_Alert::alert($report['content_user_id'], $alert['user_id'], $alert['username'], 'post', $report['content_id'], 'report_threshold', $extra);

                    break;

                    case 'profile_post':
                        $dw = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_ProfilePost');
                        $dw->setExistingData($report['content_id']);
                        $dw->set('message_state', 'moderated');
                        $dw->save();

                        $profilePost = $this->_getProfilePostModel()->getProfilePostById($report['content_id']);

                        $extra = array(
                            'link' => XenForo_Link::buildPublicLink('profile-posts', $profilePost),
                        );

                        XenForo_Model_Alert::alert($report['content_user_id'], $alert['user_id'], $alert['username'], 'profile_post', $report['content_id'], 'report_threshold', $extra);

                    break;
                }
            }
        }
    }

    public function countUniqueReportsByReportId($report_id)
    {
        return $this->_getDb()->fetchOne('
            SELECT COUNT(DISTINCT user_id)
            FROM xf_report_comment
            WHERE report_id = ?
            ', $report_id);
    }

    protected function _getUserModel()
    {
        return $this->getModelFromCache('XenForo_Model_User');
    }

    protected function _getThreadModel()
    {
        return $this->getModelFromCache('XenForo_Model_Thread');
    }

    protected function _getPostModel()
    {
        return $this->getModelFromCache('XenForo_Model_Post');
    }

    protected function _getProfilePostModel()
    {
        return $this->getModelFromCache('XenForo_Model_ProfilePost');
    }
}
