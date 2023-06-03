import { useState, useEffect, useRef } from 'react'

import * as Core from '@coreui/react';

import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';



function EmptySection(props) {

  const [visible, setVisible] = useState(false);

  const getListStyle = (isDraggingOver) => ({
    background: isDraggingOver ? "lightgreen" : "lightgrey",
  });

  const droppableId = `empty-section-${props.index}`
  return (
    <>
    <Droppable
        droppableId = { droppableId }
        type = "component"
        style = {{minHieght: `100px`}}
      >
        {(provided, snapshot) => (
          <div
           {...provided.draggableProps}
           {...provided.dragHandleProps}
           ref = {provided.innerRef}
           style={{minHieght: `100px`, height: `100px`,background: snapshot.isDraggingOver ? "lightgreen" : "lightgrey"}}
           className = "mt-4 mb-4 dnd"
          >
          <Draggable draggableId={ `section-item-0`} key={`section-item-0`} index={ 0 } disableInteractiveElementBlocking = { false }>
            {(provided, snapshot) => (
              <div
                {...provided.draggableProps}
                {...provided.dragHandleProps}
                ref = {provided.innerRef}
              >
              </div>
            )}
          </Draggable>
          { provided.placeholder }
          </div>
        )}
      </Droppable>
      </>
  )
}

export default EmptySection;
