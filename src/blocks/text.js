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
    const { heading, text } = attributes;

    return (
      <div className="text-block">
        <RichText
          tagName="h2"
          value={heading}
          onChange={(newHeading) => setAttributes({ heading: newHeading })}
          placeholder="Enter heading here..."
        />
        <RichText
          tagName="p"
          value={text}
          onChange={(newText) => setAttributes({ text: newText })}
          placeholder="Enter text here..."
        />
      </div>
    );
  },
  save: ({ attributes }) => {
    const { heading, text } = attributes;

    return (
      <div className="text-block">
        <RichText.Content tagName="h2" value={heading} />
        <RichText.Content tagName="p" value={text} />
      </div>
    );
  },
});
