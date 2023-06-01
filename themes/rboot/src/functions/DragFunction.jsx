 const onDragEnd = (data, fn) => {
   console.log(data, fn)
    // if (!data.destination) return;
    // const startIndex = data.source.index;
    // const endIndex = data.destination.index;
    // const result = [...itemsData];
    // const [removed] = fn.result.splice(startIndex, 1);
    // fn.result.splice(endIndex, 0, removed);

    // fn.setItemsData((prevItemsData) => {
    //   const result = [...prevItemsData];
    //   const resultItemData = result.map((item, index) => {
    //     if (index === startIndex || index === endIndex) {
    //       item['isDragged'] = true;
    //     } else {
    //       item['isDragged'] = false;
    //     }
    //     return item
    //   });
    //   const [removed] = resultItemData.splice(startIndex, 1);
    //   resultItemData.splice(endIndex, 0, removed);
    //   return resultItemData;
    // });
   // fn.setDragType(`component`)
  }
  const onBeforeCapture = (result, fn) => {
      // if (result.draggableId == 'card_simple') {
      //   fn.setDragType({
      //     ...fn.dragType,
      //     group: `disable`,
      //     card_group: `enable`
      //   })
      // }
      // if (result.draggableId == 'accordion') {
      //   fn.setDragType({
      //     ...fn.dragType,
      //     group: `enable`,
      //     card_group: `disable`
      //   })
      // }

    console.log(`before capture`, result)
  }

  const onBeforeDragStart = (result, fn) => {
    //  if (result.source.droppableId === 'offcanvas') {
    //   if (result.draggableId == 'card_simple') {
    //     fn.setDragType({
    //       ...fn.dragType,
    //       group: `disable`,
    //       card_group: `enable`
    //     })
    //   }
    //   if (result.draggableId == 'accordion') {
    //     fn.setDragType({
    //       ...fn.dragType,
    //       group: `enable`,
    //       card_group: `disable`
    //     })
    //   }
    // }
    console.log(`before dragStart`, result)
  }

  const onDragStart = (result, fn) => {
    // fn.setDragType(`component`)
    // if (result.source.droppableId === 'offcanvas') {
    //   if (result.draggableId == 'card_simple') {
    //     fn.setDragType((...prevData) => {
    //       prevData['group'] = 'disable';
    //       prevData['card_group'] = 'enable';
    //       return prevData;
    //     })
    //   }
    //   if (result.draggableId == 'accordion') {
    //     // fn.setDragType((...prevData) => {
    //     //   prevData['group'] = 'enable';
    //     //   prevData['card_group'] = 'disable';
    //     //   return prevData;
    //     // })
    //     fn.setDragType({...fn.dragType, group: `enable`, card_group: `disable` })
    //   }
    // console.log(`Start`, result)
    // }
  }

  const onDragUpdate = (result, fn) => {
    // if (result.source.droppableId === 'offcanvas') {
    //   if (result.destination == null) {
    //     console.log('update',result)
    //     return;
    //   }
    //   if (result.draggableId == 'card_simple'
    //     && result.destination.droppableId.includes(`group-`)) {
    //       fn.setDragType(`subComponent`)
    //   }
    //   if (result.draggableId == 'accordion'
    //     && result.destination.droppableId.includes(`group-`)) {
    //       fn.setDragType(`component`)
    //   }
    // }
    // console.log('update',result)
  }

  const getListStyle = (isDraggingOver, draggingOverWith, props, fn) => ({
    background: isDraggingOver ? "lightgreen" : "lightgrey",
  });


export default {
  onBeforeCapture,
  onBeforeDragStart,
  onDragStart,
  onDragEnd,
  onDragUpdate,
  getListStyle
};
