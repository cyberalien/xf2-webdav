<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

class Helper {
	/**
	 * Get style components
	 *
	 * @param $style
	 * @return array
	 */
	public static function getStyleComponents($style)
	{
		$items = [];

		// Template types
		/* @var \XF\Repository\Template $templateRepo */
		$templateRepo = \XF::repository('XF:Template');
		$types = $templateRepo->getTemplateTypes($style);

		foreach ($types as $type => $data) {
			$items[$type . ' templates'] = ['type', $type, 'findEffectiveTemplatesInStyle'];
			$items[$type . ' templates (customized)'] = ['type', $type, 'findTemplatesInStyle'];
		}

		// Style properties
		$items['style properties'] = ['prop', null];
		$items['style properties (customized)'] = ['prop', ['customized' => true]];
		$items['style property groups'] = ['props'];
		$items['style_properties.less'] = ['props.less'];

		return $items;
	}

	/**
	 * Get node for style component
	 *
	 * @param $style
	 * @param $name
	 * @param $data
	 * @return Node
	 */
	public static function getStyleComponentNode($style, $name, $data)
	{
		if (!is_array($data)) {
			return null;
		}
		switch ($data[0]) {
			case 'type':
				return new TemplatesType($style, $name, $data[1], $data[2]);

			case 'prop':
				return new StyleProperties($style, $name, $data[1]);

			case 'props':
				return new StylePropertyGroups($name, $style);

			case 'props.less':
				return new AllStyleProperties($style, $name);
		}
		return null;
	}

	/**
	 * Convert resource to string
	 *
	 * @param $data
	 * @return bool|string
	 */
	public static function resourceToString($data)
	{
		if (is_resource($data)) {
			$filename = tempnam(sys_get_temp_dir(), 'xf');
			file_put_contents($filename, $data);
			$data = file_get_contents($filename);
			@unlink($filename);
		}
		return $data;
	}

	/**
	 * Save template
	 *
	 * @param $styleId
	 * @param $template
	 * @param $data
	 * @throws DAV\Exception
	 */
	public static function saveTemplate($styleId, $template, $data)
	{
		$em = \XF::em();

		if ($styleId !== $template->style_id) {
			// Create new template
			$templateMap = $em->getFinder('XF:TemplateMap')
                  ->where([
	                  'style_id' => $styleId,
	                  'type' => $template->type,
	                  'title' => $template->title
                  ])
                  ->with('Template', true)
                  ->fetchOne();

			$baseTemplate = $templateMap->Template;

			if ($baseTemplate->style_id == $styleId)
			{
				// template already exists in this style
				throw new DAV\Exception\NotFound();
			}

			// template only exists in a parent; duplicate it here
			$newTemplate = $em->create('XF:Template');
			$newTemplate->style_id = $styleId;
			$newTemplate->type = $baseTemplate->type;
			$newTemplate->title = $baseTemplate->title;
			$newTemplate->template = $data;
			$newTemplate->addon_id = $baseTemplate->addon_id;
			$newTemplate->last_edit_date = time();

			if (!$newTemplate->preSave()) {
				throw new DAV\Exception\ServiceUnavailable();
			}

			$newTemplate->save();
			return;
		}

		// Update existing template
		$template = \XF::finder('XF:Template')->where([
			'style_id' => $styleId,
			'type' => $template->type,
			'title' => $template->title
		])->fetchOne();
		if (!$template) {
			throw new DAV\Exception\NotFound();
		}

		$template->setOption('check_duplicate', false);
		$template->template = $data;
		$template->last_edit_date = time();
		$template->save();
	}

	/**
	 * Save style property
	 *
	 * @param $styleId
	 * @param $prop
	 * @param $data
	 *
	 * @throws DAV\Exception\ServiceUnavailable
	 */
	public static function saveProperty($styleId, $prop, $data)
	{
		$em = \XF::em();
		if (is_string($data)) {
			$data = trim($data);
		}

		if ($styleId !== $prop->style_id) {
			// Create new property
			$newProp = $em->create('XF:StyleProperty');
			$list = $prop->toArray();
			unset($list['property_id']);
			$list['style_id'] = $styleId;
			$list['property_value'] = $data;
			$newProp->bulkSet($list);

			if (!$newProp->preSave()) {
				throw new DAV\Exception\ServiceUnavailable();
			}

			$newProp->save();
			return;
		}

		// Update existing property
		$prop = \XF::finder('XF:StyleProperty')->where([
			'style_id' => $styleId,
			'property_name' => $prop->property_name
		])->fetchOne();
		if (!$prop) {
			throw new DAV\Exception\ServiceUnavailable();
		}
		$prop->property_value = $data;

		if (!$prop->preSave()) {
			throw new DAV\Exception\ServiceUnavailable();
		}
		$prop->save();
	}
}
