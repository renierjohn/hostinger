import { SideBarData } from '../api/SideBarData';
import * as drag_fn from '../functions/DragFunction'
import * as util_fn from '../functions/UtilsFunction'

import * as Core from '@coreui/react';
import { useState, useEffect, useRef } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';

function OffCanvas(props) {

  const [components, setComponents] = useState(SideBarData);

  const getListStyle = drag_fn.default.getListStyle;

  const _fn = {
    setComponents: setComponents,
    components: components
  };

  return (
    <>
    <Core.COffcanvas backdrop={false} placement="start" scroll={true} visible={props.visible} onHide={() => props.setVisible(false)}>
        <Core.COffcanvasHeader>
          <Core.COffcanvasTitle>Components</Core.COffcanvasTitle>
          <Core.CCloseButton className="text-reset" onClick={() => props.setVisible(false)} />
        </Core.COffcanvasHeader>
        <Core.COffcanvasBody>
          <Core.CListGroup>
              <Droppable
                droppableId="offcanvas"
                type="enable"
              >
              {(provided, snapshot) => (
               <div
                 {...provided.droppableProps}
                 ref={provided.innerRef}
                 style={getListStyle(snapshot.isDraggingOver, snapshot.draggingOverWith, props, _fn)}
                 className = "row"
               >
                {components.map((item, index) => (
                    <Draggable draggableId={ item.id } key={ item.id } index={index} disableInteractiveElementBlocking = { false }>
                      {(provided, snapshot) => (
                        <div
                          {...provided.draggableProps}
                          {...provided.dragHandleProps}
                          ref = {provided.innerRef}
                          className="border border-primary offcanvas-style p-2 col-md-6 col-lg-6"
                        >
                          <div className="fw-bold text-center">{item.name}</div>
                          <div>
                            <Core.CImage rounded thumbnail src = {item.icon} width={200} height={200} />
                          </div>
                        </div>
                      )}
                    </Draggable>
                  ))}
               { provided.placeholder }
               </div>
              )}
              </Droppable>

          </Core.CListGroup>
       </Core.COffcanvasBody>
      </Core.COffcanvas>
      </>
  )
}

export default OffCanvas;
