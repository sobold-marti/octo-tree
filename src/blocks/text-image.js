const { registerBlockType } = wp.blocks;
const { MediaUpload, RichText } = wp.blockEditor;
const { Button } = wp.components;

registerBlockType('custom/text-image', {
  title: 'Text and Image Block',
  icon: 'format-image',
  category: 'common',
  attributes: {
    heading: {
      type: 'string',
      source: 'html',
      selector: 'h2',
    },
    text: {
      type: 'string',
      source: 'html',
      selector: 'p',
    },
    imageUrl: {
      type: 'string',
      source: 'attribute',
      selector: 'img',
      attribute: 'src',
    },
  },
  edit: ({ attributes, setAttributes }) => {
    const { heading, text, imageUrl } = attributes;

    return (
      <div className="text-image-block">
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
        <MediaUpload
          onSelect={(media) => setAttributes({ imageUrl: media.url })}
          allowedTypes={['image']}
          render={({ open }) => (
            <Button onClick={open} style={{ width: '100%', height: '100%' }}>
              {imageUrl ? (
                <img src={imageUrl} alt="Block Image" style={{ width: '100%' }} />
              ) : (
                'Select Image'
              )}
            </Button>
          )}
        />
      </div>
    );
  },
  save: ({ attributes }) => {
    const { heading, text, imageUrl } = attributes;

    return (
      <div className="text-image-block">
        <RichText.Content tagName="h2" value={heading} />
        <RichText.Content tagName="p" value={text} />
        {imageUrl && <img src={imageUrl} alt="Block Image" />}
      </div>
    );
  },
});
