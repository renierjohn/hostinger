import { ComponentBaseData } from '../redux/store';

const data =
  {
    'id': 1,
    'key': `group`,
    'type': `paragraph`,
    'title': `Accordion Sample`,
    'subtitle': `Lorem ipsum subtitle`,
    'list':[
      {
      'id': 201,
      },
      {
        'id': 202,
      },
      {
        'id': 203,
      }
    ]
  }

export const GroupData = ComponentBaseData(data);

export default {
    GroupData,
}
