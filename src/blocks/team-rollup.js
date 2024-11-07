const { registerBlockType } = wp.blocks;
const { RichText, useBlockProps } = wp.blockEditor;
const { CheckboxControl } = wp.components;
const { useSelect } = wp.data;

registerBlockType('custom/team-rollup', {
  title: 'Team Rollup Block',
  icon: 'groups',
  category: 'common',
  attributes: {
    headingTr: {
      type: 'string',
      source: 'html',
      selector: 'h2',
    },
    teamSelect: {
      type: 'array',
      default: [], // Array for multiple selected team member IDs
    },
  },
  edit: ({ attributes, setAttributes }) => {
    const { headingTr, teamSelect } = attributes;

    // Fetch team posts
    const teamPosts = useSelect((select) => {
      return select('core').getEntityRecords('postType', 'team', { per_page: -1 });
    }, []);

    const teamOptions = teamPosts
      ? teamPosts.map((post) => ({ label: post.title.rendered, value: post.id }))
      : [];

    // Toggle function to add/remove selected team member IDs
    const toggleTeamMember = (postId) => {
      if (teamSelect.includes(postId)) {
        setAttributes({ teamSelect: teamSelect.filter((id) => id !== postId) });
      } else {
        setAttributes({ teamSelect: [...teamSelect, postId] });
      }
    };

    return (
      <div {...useBlockProps()}>
        {/* RichText component for the heading */}
        <RichText
          tagName="h2"
          value={headingTr}
          onChange={(newHeading) => setAttributes({ headingTr: newHeading })}
          placeholder="Enter heading here..."
        />

        {/* Checkboxes for selecting multiple team members directly in the block */}
        <div className="team-selection">
          <p>Select Related Team Members:</p>
          {teamOptions.length > 0 ? (
            teamOptions.map((option) => (
              <CheckboxControl
                key={option.value}
                label={option.label}
                checked={teamSelect.includes(option.value)}
                onChange={() => toggleTeamMember(option.value)}
              />
            ))
          ) : (
            <p>No team members found.</p>
          )}
        </div>
      </div>
    );
  },
  save: ({ attributes }) => {
    const { headingTr, teamSelect } = attributes;

    return (
      <div className="team-rollup-block">
        {/* Render the heading */}
        <RichText.Content tagName="h2" value={headingTr} />
        
        {/* Store selected team member IDs as a data attribute */}
        <div className="related-post-ids" data-ids={teamSelect.join(',')}>
          {/* The selected team member IDs are stored here */}
        </div>
      </div>
    );
  },
});
