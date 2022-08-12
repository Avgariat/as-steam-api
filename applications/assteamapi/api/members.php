<?php

namespace IPS\assteamapi\api;

/* To prevent PHP errors (extending class does not exist) revealing path */

if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class _members extends \IPS\Api\Controller
{
    /**
     * GET /assteamapi/members
     * Get list of members
     *
     * @apiparam	string	sortBy		    What to sort by. Can be 'joined', 'name', 'last_activity' or leave unspecified for ID
     * @apiparam	string	sortDir		    Sort direction. Can be 'asc' or 'desc' - defaults to 'asc'
     * @apiparam	string	steamId         user steam id 64 to search for
     * @apiparam	bool	isSteamPartial  should user steam id 64 be searched in partial mode - defaults to 0
     * @apiparam	int		page		    Page number
     * @apiparam	int		perPage		    Number of results per page - defaults to 25
     * @return		\IPS\Api\PaginatedResponse<IPS\Member>
     */
    public function GETindex()
    {
        /* Where clause */
        $where = array( array( 'core_members.email<>?', '' ) );

        /* Are we searching? */
        if (isset(\IPS\Request::i()->steamId)) {
            if (isset(\IPS\Request::i()->isSteamPartial) && \IPS\Request::i()->isSteamPartial) {
                $where[] = \IPS\Db::i()->like('steamId', \IPS\Request::i()->steamId);
            } else {
                $where[] = ['steamId = ?', \IPS\Request::i()->steamId];
            }
        } else {
            return new \IPS\Api\PaginatedResponse(
                200,
                new \EmptyIterator,
                1,
                'IPS\Member',
                isset(\IPS\Request::i()->perPage) ? \IPS\Request::i()->perPage : null
            );
        }

        /* Sort */
        $sortBy = (
            isset(\IPS\Request::i()->sortBy)
            and \in_array(\IPS\Request::i()->sortBy, array( 'name', 'joined', 'last_activity' ))
        ) ? \IPS\Request::i()->sortBy : 'member_id';
        $sortDir = (
            isset(\IPS\Request::i()->sortDir)
            and \in_array(mb_strtolower(\IPS\Request::i()->sortDir), array( 'asc', 'desc' ))
        ) ? \IPS\Request::i()->sortDir : 'asc';

        /* Return */
        return new \IPS\Api\PaginatedResponse(
            200,
            \IPS\Db::i()->select('*', 'core_members', $where, "{$sortBy} {$sortDir}"),
            isset(\IPS\Request::i()->page) ? \IPS\Request::i()->page : 1,
            'IPS\Member',
            \IPS\Db::i()->select('COUNT(*)', 'core_members', $where)->first(),
            $this->member,
            isset(\IPS\Request::i()->perPage) ? \IPS\Request::i()->perPage : null
        );
    }
}
