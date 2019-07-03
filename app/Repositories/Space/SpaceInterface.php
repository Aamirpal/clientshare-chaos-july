<?php

namespace App\Repositories\Space;

interface SpaceInterface
{
	public function communitySpaceInfo($space_id);
	public function allSpacesWithSelectedColoumn($columns = []);
    public function getAllSpaces();
    public function update(array $space_data, $space_id);
    public function isBusinessReviewEnabled($space_id);
    public function saveTwitterHandler($request);
    public function getTwitterHandler($space_id);
    public function shareCategorySheet();
    public function migrateVersion($version, $space_ids);
    Public function getRenameProcessLogoShares();
    public function renameProcessLogo($space);
}