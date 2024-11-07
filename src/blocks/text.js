const { registerBlockType } = wp.blocks;
const { RichText } = wp.blockEditor;

registerBlockType('custom/text', {
  title: 'Text Block',
  icon: 'format-image',
  category: 'common',
  attributes: {
    headingTb: {
      type: 'string',
      source: 'html',
      selector: 'h2',
    },
    textTb: {
      type: 'string',
      source: 'html',
      selector: 'p',
    },
  },
  edit: ({ attributes, setAttributes }) => {
    const { headingTb, textTb } = attributes;

    return (
      <div className="text-block">
        <RichText
          tagName="h2"
          value={headingTb}
          onChange={(newHeading) => setAttributes({ headingTb: newHeading })}
          placeholder="Enter heading here..."
        />
        <RichText
          tagName="p"
          value={textTb}
          onChange={(newText) => setAttributes({ textTb: newText })}
          placeholder="Enter text here..."
        />
      </div>
    );
  },
  save: ({ attributes }) => {
    const { headingTb, textTb } = attributes;

    return (
      <div className="text-block">
        <RichText.Content tagName="h2" value={headingTb} />
        <RichText.Content tagName="p" value={textTb} />
      </div>
    );
  },
});
