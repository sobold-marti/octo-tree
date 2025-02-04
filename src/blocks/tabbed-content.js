const { registerBlockType } = wp.blocks;
const { useSelect } = wp.data;
const { RichText, BlockControls } = wp.blockEditor;
const { SelectControl, Button } = wp.components;

registerBlockType("custom/tabbed-content", {
  title: "Tabbed Content",
  icon: "welcome-widgets-menus",
  category: "common",
  attributes: {
    mainHeading: { type: "string", default: "" },
    tabOneTitle: { type: "string", default: "" },
    tabTwoTitle: { type: "string", default: "" },
    tabThreeTitle: { type: "string", default: "" },
    tabOneContent: { type: "array", default: [] },
    tabTwoContent: { type: "array", default: [] },
    tabThreeContent: { type: "array", default: [] },
  },
  edit: ({ attributes, setAttributes }) => {
    const {
      mainHeading,
      tabOneTitle,
      tabTwoTitle,
      tabThreeTitle,
      tabOneContent,
      tabTwoContent,
      tabThreeContent,
    } = attributes;

    // Function to fetch posts
    const useFetchPosts = (postType) => {
      return useSelect((select) => {
        return select("core").getEntityRecords("postType", postType, { per_page: 10 });
      }, [postType]);
    };
    const lessons = useFetchPosts("lessons") || [];
    const team = useFetchPosts("team") || [];
    const posts = useFetchPosts("post") || [];

    // Function to update selected posts
    const updateSelectedPosts = (tab, selectedPostId) => {
      const selectedPosts = attributes[tab];

      // Check if the post is already selected
      if (selectedPosts.some((post) => post.id === selectedPostId)) {
        return;
      }

      const selectedPost = [...lessons, ...team, ...posts].find((p) => p.id === selectedPostId);
      if (selectedPost) {
        const newSelection = [
          ...selectedPosts,
          {
            id: selectedPost.id, 
            title: selectedPost.title.rendered,
            slug: selectedPost.slug,
            postType: selectedPost.type === 'post' ? 'blog' : selectedPost.type,
          },
        ];
        setAttributes({ [tab]: newSelection });
      }
    };

    // Function to remove a post from selection
    const removePost = (tab, postId) => {
      setAttributes({ [tab]: attributes[tab].filter((post) => post.id !== postId) });
    };

    return (
      <div className="tabbed-content-block">
        <RichText
          tagName="h2"
          className="main-heading"
          value={mainHeading}
          onChange={(value) => setAttributes({ mainHeading: value })}
          placeholder="Enter main heading..."
        />
        {[{ tab: "tabOne", title: tabOneTitle, setTitle: "tabOneTitle", posts: lessons, content: tabOneContent },
          { tab: "tabTwo", title: tabTwoTitle, setTitle: "tabTwoTitle", posts: team, content: tabTwoContent },
          { tab: "tabThree", title: tabThreeTitle, setTitle: "tabThreeTitle", posts: posts, content: tabThreeContent }]
          .map(({ tab, title, setTitle, posts, content }) => (
            <div className="tab" key={tab}>
              {/* Make sure title is editable and saved properly */}
              <RichText
                tagName="h3"
                className={`${tab}-title`}
                value={title}
                onChange={(value) => setAttributes({ [setTitle]: value })}
                placeholder={`Enter title for ${tab.replace("tab", "Tab ")}...`}
              />

              <SelectControl
                label="Select Posts"
                value=""
                options={[
                  { value: "", label: "Select a post..." },
                  ...posts.map((post) => ({ value: post.id, label: post.title.rendered })),
                ]}
                onChange={(postId) => updateSelectedPosts(`${tab}Content`, parseInt(postId))}
              />

              <ul>
                {content.length > 0 ? (
                  content.map((post) => (
                    <li key={post.id}>
                      {post.title}{" "}
                      <Button isDestructive onClick={() => removePost(`${tab}Content`, post.id)}>
                        Remove
                      </Button>
                    </li>
                  ))
                ) : (
                  <p>No content selected</p>
                )}
              </ul>
            </div>
          ))}
      </div>
    );
  },
  save: ({ attributes }) => {
    const { mainHeading, tabOneTitle, tabTwoTitle, tabThreeTitle, tabOneContent, tabTwoContent, tabThreeContent } = attributes;
    return (
      <div className="tabbed-content-block">
        <h2 className="tabbed-content-main-heading">{mainHeading}</h2>
        {[{ title: tabOneTitle, content: tabOneContent },
          { title: tabTwoTitle, content: tabTwoContent },
          { title: tabThreeTitle, content: tabThreeContent }]
          .map(({ title, content }, i) => (
            <div className="tab" key={i}>
              {/* Render titles properly in save function */}
              <h3>{title}</h3>
              <ul>
                {content.length > 0 ? (
                  content.map((post) => (
                    <li key={post.id}>
                      <a href={`/${post.postType}/${post.slug}`}>{post.title}</a>
                    </li>
                  ))
                ) : (
                  <p>No content available</p>
                )}
              </ul>
            </div>
          ))}
      </div>
    );
  },
});
