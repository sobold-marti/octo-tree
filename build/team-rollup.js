(()=>{"use strict";const e=window.ReactJSXRuntime,{registerBlockType:t}=wp.blocks,{RichText:a,useBlockProps:l}=wp.blockEditor,{CheckboxControl:s}=wp.components,{useSelect:r}=wp.data;t("custom/team-rollup",{title:"Team Rollup Block",icon:"groups",category:"common",attributes:{headingTr:{type:"string",source:"html",selector:"h2"},teamSelect:{type:"array",default:[]}},edit:({attributes:t,setAttributes:c})=>{const{headingTr:o,teamSelect:i}=t,n=r((e=>e("core").getEntityRecords("postType","team",{per_page:-1})),[]),d=n?n.map((e=>({label:e.title.rendered,value:e.id}))):[];return(0,e.jsxs)("div",{...l(),children:[(0,e.jsx)(a,{tagName:"h2",value:o,onChange:e=>c({headingTr:e}),placeholder:"Enter heading here..."}),(0,e.jsxs)("div",{className:"team-selection",children:[(0,e.jsx)("p",{children:"Select Related Team Members:"}),d.length>0?d.map((t=>(0,e.jsx)(s,{label:t.label,checked:i.includes(t.value),onChange:()=>{return e=t.value,void(i.includes(e)?c({teamSelect:i.filter((t=>t!==e))}):c({teamSelect:[...i,e]}));var e}},t.value))):(0,e.jsx)("p",{children:"No team members found."})]})]})},save:({attributes:t})=>{const{headingTr:l,teamSelect:s}=t;return(0,e.jsxs)("div",{className:"team-rollup-block",children:[(0,e.jsx)(a.Content,{tagName:"h2",value:l}),(0,e.jsx)("div",{className:"related-post-ids","data-ids":s.join(",")})]})}})})();