
import { GroupData } from './api/GroupData';
import { AccordionData } from './api/AccordionData';
import { NodeData } from './api/NodeData';

import CardGroup from './components/CardGroupDrag'
import Group from './components/GroupDrag'
import Modal from './components/ModalCore'
import OffCanvas from './components/OffCanvas'
import EmptySection from './components/EmptySection'
import * as drag_fn from './functions/DragFunction'

import { useState, useEffect, useRef } from 'react'
import * as Core from '@coreui/react';

import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';

import '../css/bootstrap.css'
import './style.css'

function App() {

  const [visible, setVisible] = useState(false);

  const [components, setComponents] = useState(NodeData.component_body);

  const [renderComponents, setRenderComponents] = useState();

  const [dragType, setDragType] = useState({
    'group': `enable`,
    'card_group': `enable`
  });

  const onDragStart = drag_fn.default.onDragStart;

  const onDragEnd = drag_fn.default.onDragEnd;

  const onDragUpdate = drag_fn.default.onDragUpdate;

  const onBeforeDragStart = drag_fn.default.onBeforeDragStart;

  const onBeforeCapture = drag_fn.default.onBeforeCapture;

  const _fn = {
    setVisible: setVisible,
    setDragType: setDragType,
    setComponents: setComponents,
    dragType: dragType,
    components: components,
    visible: visible,
  };

  useEffect(() =>{
    const render = components.map((item, index) => {
      if (item.key === 'group') {
        return <Group { ...item } dragType = { dragType.group } key = { index } ><EmptySection index = { index } /></Group>
      }
      if (item.key === 'card_group') {
        return <CardGroup { ...item } dragType = { dragType.card_group } key = { index } ><EmptySection index = { index } /></CardGroup>
      }
    });
    setRenderComponents(render);

  },[dragType])

  return (
    <div className="container">
    {/* OFF CANVAS BUTTON */}
    <Core.CButton className = "mt-2 mb-2" onClick={() => setVisible(true)}>
      Show Components
    </Core.CButton>

    <DragDropContext
      onDragStart = { (e) => onDragStart(e, _fn)}
      onDragEnd = { (e) => onDragEnd(e, _fn) }
      onDragUpdate = { (e) => onDragUpdate(e, _fn) }
      onBeforeDragStart = { (e) => onBeforeDragStart(e, _fn) }
      onBeforeCapture = { (e) => onBeforeCapture(e, _fn) }

    >
      <OffCanvas visible = { visible } setVisible = { setVisible } />
      { renderComponents }
    </DragDropContext >

    </div>
  )
}

export default App
