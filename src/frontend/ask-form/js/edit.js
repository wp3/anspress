import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, SelectControl, SearchControl, RangeControl, ToggleControl, TextControl, Button } from '@wordpress/components';
import { useState } from 'react';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import { ProNoticeComponent } from '../../common/js/ProNoticeComponent';

const Edit = ({ attributes, setAttributes }) => {
  const { currentQuestionId } = attributes;

  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <InspectorControls>
        <ProNoticeComponent />

      </InspectorControls>

      <ServerSideRender block="anspress/ask-form" attributes={attributes} />
    </div>
  );
};

export default Edit;