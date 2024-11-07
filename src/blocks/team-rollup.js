import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { CheckboxControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

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
      default: [],
    },
  },
  edit: ({ attributes, setAttributes }) => {
    const { headingTr, teamSelect } = attributes;
    const blockProps = useBlockProps();
      
    // Fetch team posts
    const teamPosts = useSelect((select) => {
      return select('core').getEntityRecords('postType', 'team', { per_page: -1 });
    }, []);

    const teamOptions = teamPosts
      ? teamPosts.map((post) => ({ label: post.title.rendered, value: post.id }))
      : [];

    // Toggle team member selection
    const toggleTeamMember = (postId) => {
      const newSelection = teamSelect.includes(postId)
        ? teamSelect.filter((id) => id !== postId)
        : [...teamSelect, postId];
      setAttributes({ teamSelect: newSelection });
    };

    // Handle reordering with react-beautiful-dnd
    const handleDragEnd = (result) => {
      if (!result.destination) return;

      const reorderedTeamSelect = Array.from(teamSelect);
      const [movedItem] = reorderedTeamSelect.splice(result.source.index, 1);
      reorderedTeamSelect.splice(result.destination.index, 0, movedItem);

      setAttributes({ teamSelect: reorderedTeamSelect });
    };

    return (
      <div {...blockProps}>
        {/* RichText component for the heading */}
        <RichText
          tagName="h2"
          value={headingTr}
          onChange={(newHeading) => setAttributes({ headingTr: newHeading })}
          placeholder="Enter heading here..."
        />

        {/* Checkboxes for selecting team members */}
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

        {/* Drag-and-drop reordering for selected team members */}
        <DragDropContext onDragEnd={handleDragEnd}>
          <Droppable droppableId="teamMembers">
            {(provided) => (
              <div
                ref={provided.innerRef}
                {...provided.droppableProps}
                className="selected-team-list"
              >
                {teamSelect.map((id, index) => {
                  const member = teamOptions.find((option) => option.value === id);
                  return member ? (
                    <Draggable key={id} draggableId={id.toString()} index={index}>
                      {(provided) => (
                        <div
                          ref={provided.innerRef}
                          {...provided.draggableProps}
                          {...provided.dragHandleProps}
                          className="sortable-item"
                        >
                          <p>{member.label}</p>
                        </div>
                      )}
                    </Draggable>
                  ) : null;
                })}
                {provided.placeholder}
              </div>
            )}
          </Droppable>
        </DragDropContext>
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
        <div className="related-post-ids" data-ids={teamSelect.join(',')} />
      </div>
    );
  },
});
