import { NodeData } from './api/NodeData';
import RestData from './api/RestData';
import RestNodeData from './api/RestNodeData';

import Wrapper from './components/Wrapper'
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

  const { restData, restLoading } = RestData({ key: `node/754` });

  // const { restNodeData, restNodeLoading } = RestNodeData({ uuid: `31bde1ab-780f-4a39-a69e-ca33f0e27ad7` });

  const attribs = [
    'second-level-class',
    'first-level-class',
  ]
  const _fn = {
    setVisible: setVisible,
    setDragType: setDragType,
    setComponents: setComponents,
    dragType: dragType,
    components: components,
    visible: visible,
  };

  useEffect(() => {
//     if (!restLoading) {
//       const banner = restNodeData['data']['relationships']['field_component_banner'];
//       const components = restNodeData['data']['relationships']['field_components'];
// console.log(banner, components)
//     }

    if (!restLoading) {
      const render = components.map((item, index) => {
        if (item.machine_name === 'group') {
          return <Group { ...item }  dragType = { dragType.group } key = { index } ><EmptySection index = { index } /></Group>
        }
        if (item.machine_name === 'card_group') {
          return <CardGroup { ...item }  dragType = { dragType.card_group } key = { index } ><EmptySection index = { index } /></CardGroup>
        }
      });

      setRenderComponents(render);
    }

  },[restLoading, dragType])

  return (
    <Wrapper attribs = { attribs } >
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
        { restLoading ? <h2 className="display-3">Loading Components...</h2> : renderComponents }
      </DragDropContext >
    </div>
    </Wrapper>
  )
}

export default App
